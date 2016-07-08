<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 16:07
 */

namespace Home\Model;


use Think\Model;
use Think\Verify;

class MemberModel extends Model
{
    //开启批量验证
    protected $pathValidate=true;
    //验证表单数据
    //后台只需要验证用户名和邮箱重复与否,其余的必填,手机号格式,邮箱格式,在前台用jquery-validate验证
    protected $_validate=[
        ['username','','用户名不能重复',self::EXISTS_VALIDATE,'unique','reg'],
        ['email','','邮箱不能重复',self::EXISTS_VALIDATE,'unique'],
//        ['checkcode','checkcode','验证码不正确',self::EXISTS_VALIDATE,'callback'],
        ['captcha','checktelcode','手机验证码不正确',self::EXISTS_VALIDATE,'callback'],
    ];
    //自动完成, 生成盐,注册时间,状态(首次注册还未验证的时候,状态为0),
    protected $_auto=[
        ['salt','\Org\Util\String::randString','reg','function'],
        //生成邮箱的令牌码
        ['register_token','\Org\Util\String::randString','reg','function',32],
        ['add_time',NOW_TIME,'reg'],
        ['status',0,'reg'],
    ];
    /**
     * 验证手机验证码
     * @param $telcode
     * @return bool
     */
    protected function checktelcode($telcode){
        if($telcode==session('reg_tel_code')){
            session('reg_tel_code',null);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证图片验证码
     * @param $code
     */
    protected function checkcode($code)
    {
        $setting=['length'=>4];
        $verify=new Verify($setting);
        return $verify->check($code);
    }

    /**
     * 添加用户,同时验证邮箱
     * @return bool
     */
    public function addMember()
    {
        //密码加盐
        $this->data['password']=salt_mcrypt($this->data['password'],$this->data['salt']);
        $register_token=$this->data['register_token'];
        $email=$this->data['email'];
        $username=$this->data['username'];
//        dump($this->data());exit;
        if($this->add()===false){
            return false;
        }
        //发送邮件验证,由于邮件发送很多地方都用,因此放在公共function中,这边调用的时候分别传入4个值,分别是用户输入的邮箱地址,标题信息,邮件内容,用户名
        //这里url传两个值,邮箱和一个类似于令牌的随机字符串,保存到数据库,这样当点击这个链接触发验证的时候,就可以从数据库取出数据和浏览器url上面的值做对比,相同则激活
        $url=U('Member/checkEmail',['email'=>$email,'register_token'=>$register_token],true,true);
        $subject='欢迎注册Nolyn彼倫杂货铺';
        $content='欢迎注册我们的网站,请点击<a href="'.$url.'">链接</a>激活账号.无法跳转请复制以下网站直接在浏览器打开!</br>'.$url;
        $rst=sendEmail($email,$subject,$content,$username);
        if(!$rst['status']){
            $this->error=$rst['msg'];

            return false;
        }
        return true;
    }

    /**
     * 验证用户登陆
     * @return bool
     */
    public function login()
    {
        $username=$this->data['username'];
        $password=$this->data['password'];
       if(!$userinfo=$this->getByUsername($username)){
           $this->error='用户名或密码错误';
           return false;
       }
        $pwd=salt_mcrypt($password,$userinfo['salt']);
        if($pwd!=$userinfo['password']){
            $this->error='用户名或密码错误';
            return false;
        }
        //保存用户最后登陆时间和ip
        $data=[
            'id'=>$userinfo['id'],
            'last_login_time'=>NOW_TIME,
            'last_login_ip'=>get_client_ip(1)
        ];
        $this->setField($data);
        login($userinfo);
        //验证是否勾选保存登陆信息,用于自动登陆
        if(I('post.remember')){
            $this->_save_token($userinfo['id']);
        }
        return $userinfo;
    }
    /**
     * 保存令牌,如果用户勾选了保存登陆信息,则执行
     * @param $member_id
     * @return bool
     */
    private function _save_token($member_id){
        $member_token_model=M('MemberToken');
        //第二次则删除当前Token,重新添加
        $member_token_model->delete($member_id);
        //拼接数据
        $cond=[
            'member_id'=>$member_id,
            'token'=>\Org\Util\String::randString(40),
        ];
        //登陆的时候,勾选了保存登陆信息,将cookie保存一个星期
        auto_login($cond);
        return $member_token_model->add($cond);
    }
    /**
     * 验证自动登陆
     */
    public function autologin()
    {
        $cookie=auto_login();
        if(!$cookie){
//            echo 1;
            return false;
        }
        //验证cookie里面的token值
        $member_token_model=M('MemberToken');
        if(!$member_token_model->where($cookie)->count()){
            return false;
        }
        //为了防止被窃取,自动登陆一次后就重置token
        $this->_save_token($cookie['member_id']);
        //自动登陆成功,并将用户信息保存到session
        $userinfo=$this->find($cookie['member_id']);
        login($userinfo);
        //获取并保存用户权限
        return $userinfo;
    }

}