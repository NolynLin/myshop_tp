<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/2
 * Time: 10:10
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;
use Think\Verify;

class AdminModel extends Model
{
    //开启批量验证
    protected $patchValidate    =   true;
    //自动验证
    //管理员名称必填  唯一
    //密码必填 两次密码一致 长度在6-16
    //邮箱必填 唯一
    protected $_validate=[
      ['username','require','管理员名称不能为空'],
      ['username','','管理员名称不能重复',self::EXISTS_VALIDATE,'unique','register'],
      ['password','require','密码不能为空'],
      ['repassword','password','两次密码不一致',self::EXISTS_VALIDATE,'confirm'],
      ['password','6,16','密码长度不合法',self::EXISTS_VALIDATE,'length'],
        //重置密码时,如果输入了值,就去验证长度是否合法,这样就能避免自己输入密码位数不够,
       // 自动验证是验证表单数据,只要第一个参数写成input标签的name值就好了,并不是只会验证数据表存在的字段,这里用存在值就验证的方式
      ['resetpwd','6,16','密码长度不合法',self::VALUE_VALIDATE,'length'],
      ['email','require','邮箱必填'],
      ['email','email','邮箱格式不合法'],
      ['email','','邮箱已存在',self::EXISTS_VALIDATE,'unique'],
//      ['captcha','checkCaptcha','验证码不正确',self::EXISTS_VALIDATE,'callback'],
    ];
    protected $_auto =[
      ['add_time',NOW_TIME,self::MODEL_INSERT],
      ['salt','\Org\Util\String::randString',self::MODEL_BOTH,'function'],
        //这样写回调函数的时候,是获取不到值吗?是因为数据库没resetpwd这个字段吗?
      ['resetpwd','resetPwd',self::MODEL_UPDATE,'callback'],
    ];

    /**
     * 验证验证码
     * @param $code
     */
    protected function checkCaptcha($code){
        $verify=new Verify();
        return $verify->check($code);
    }
    /**
     * 添加管理员
     * @return bool
     */
    public function addAdmin()
    {
        $this->startTrans();
        $this->data['password']=salt_mcrypt($this->data['password'],$this->data['salt']);
//        dump($this->data());exit;
        //添加管理员
        if(($admin_id=$this->add())===false){
            $this->error='添加管理员失败';
            $this->rollback();
            return false;
        }
        //添加管理员对应角色
        $admin_role_model=M('AdminRole');
        $data=[];
        $roleIds=I('post.role_id');
        if($roleIds){
            foreach($roleIds as $roleId){
                $data[]=['admin_id'=>$admin_id,'role_id'=>$roleId];
            }
            if($admin_role_model->addAll($data)===false){
                $this->error='添加管理员角色关联失败';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 获取管理员分页数据
     * @param array $cond
     * @return array
     */

    public function getPageAdmin(array $cond=[])
    {
        //获取分页数据
        $count=$this->where($cond)->count();
        //获取分页配置
        $page_setting=C('PAGE_SETTING');
        $page=new Page($count,$page_setting['PAGE_SIZE']);
        //设置分页样式
        $page->setConfig('theme',$page_setting['PAGE_THEME']);
        $page_html=$page->show();
        $rows=$this->where($count)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('rows','page_html');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAdminInfo($id)
    {
        //获取管理员基本信息
        $row=$this->find($id);
        //获取管理员的角色id
        $admin_role_model=M('AdminRole');
        $roleid=$admin_role_model->where(['admin_id'=>$id])->getField('role_id',true);
        $row['role_ids']=json_encode($roleid);
//        dump($roleid);exit;
        return $row;
    }

    /**
     * 修改管理员的权限
     * @param $id
     * @return bool
     */
    public function saveAdmin($id)
    {
        $admin_role_model=M('AdminRole');
        $this->startTrans();
        //删除原有的角色
        if($admin_role_model->where(['admin_id'=>$id])->delete()===false){
            $this->error='删除管理员对应角色失败';
            $this->rollback();
            return false;
        }
        //修改管理员对应角色
        $data=[];
        $roleIds=I('post.role_id');
        if($roleIds){
            foreach($roleIds as $roleId){
                $data[]=['admin_id'=>$id,'role_id'=>$roleId];
            }
            if($admin_role_model->addAll($data)===false){
                $this->error='修改管理员角色关联失败';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 1.删除管理员基本信息
     * 2.删除管理员对应的角色
     * @param $id
     * @return bool
     */
    public function removeAdmin($id)
    {
        $this->startTrans();
        //删除管理员
        if($this->delete($id)===false){
            $this->error='删除管理员失败';
            $this->rollback();
            return false;
        }
        //删除管理员对应的角色
        $admin_role_model=M('AdminRole');
        if($admin_role_model->where(['admin_id'=>$id])->delete()===false){
            $this->error='删除管理员对应角色失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 重置管理员密码,规则,
     * 1.如果输入了密码则以输入的为准,
     * 2.如果没输入,自动生成一个6位的随机字母数字组合的六位字符串
     * @param $id
     */
    public function repasswordAdmin()
    {
        $new_pwd=$this->data['resetpwd'];
        $this->data['password']=salt_mcrypt($this->data['resetpwd'],$this->data['salt']);
        unset($this->data['resetpwd']);
        if($this->save()===false){
            return false;
        }
        return $new_pwd;
    }

    /**
     * 检测是否输入了密码,如果有值,则按照填的数据,没值则随机生成一个六位字母数字组合
     * @param $pwd
     */
    protected function resetPwd(){
        $pwd=I('post.resetpwd');
        if($pwd){
            return $pwd;
        }
        $autoPwd="FFASFfjsafjasl4657654dsad65D4SA65F4SA54DA654fsa65f4sa5f4as7w8edas654df";
        //随机打乱字符串
        $str=  str_shuffle($autoPwd);
        //取出6个字符
        return substr($str, 0,6);
    }

    /**
     * 登陆验证
     */
    public function adminLogin()
    {
        unset($this->data['salt']);
        unset($this->data['add_time']);
        $username=$this->data['username'];
        $userPwd=$this->data['password'];
        $userinfo=$this->getByUsername($username);
        //验证用户名
        if(!$userinfo){
            $this->error='用户名或密码错误';
            return false;
        }
        //验证密码
        $userPwd=salt_mcrypt($userPwd,$userinfo['salt']);
        if($userinfo['password']!=$userPwd){
            $this->error='用户名或密码错误';
            return false;
        }
        //将最后登陆时间和ip保存到数据库
        $data=[
            'id'=>$userinfo['id'],
            'last_login_ip'=>get_client_ip(1),
            'last_login_time'=>NOW_TIME,
        ];
        $this->save($data);
        //将用户的登陆信息保存到session
        login($userinfo);
        //用app_begin后,为什么session里面的数据和我设置的不一样?并且去行为那里的时候,获取不到session????因为去行为的时候,还没有控制器呢,所以没有session
//        dump(session('USER_INFO'));exit;
        //获取用户权限,单独写成一个方法
        $this->getPermissions($userinfo['id']);
        //验证是否勾选保存登陆信息,用于自动登陆
        if(I('post.remember')){
            $this->_save_token($userinfo['id']);
        }
//        dump(1);exit;
        return $userinfo;
    }

    /**
     * 获取当前用户拥有的访问权限
     * @param $admin_id
     * @return bool
     */
    private function getPermissions($admin_id){
        $cond=[
          'path'=>['neq',''],
           'ar.admin_id'=>$admin_id,
        ];
        //这里在获取用户可访问权限路径的时候,还要获取到对应的权限id,最后拿到权限-菜单表去查询当前用户可访问的菜单有哪些,
       $permissions= M()->field('permission_id,path')->table('admin_role')->distinct(true)->alias('ar')->join('__ROLE_PERMISSION__ as rp ON rp.`role_id` = ar.`role_id`')->join('__PERMISSION__ as p ON rp.`permission_id`=p.id')->where($cond)->select();
        //将路径保存到session中,转成一维数组
        $pids=[];
        $paths=[];
        foreach($permissions as $permission){
            $paths[]=$permission['path'];
            $pids[]=$permission['permission_id'];
        }
        //保存可访问的权限路径
        permission_pathes($paths);
        //保存可访问的权限id
        permission_pids($pids);
        return true;
    }

    /**
     * 保存令牌,如果用户勾选了保存登陆信息,则执行
     * @param $admin_id
     * @return bool
     */
    private function _save_token($admin_id){
        $admin_token_model=M('AdminToken');
        //第二次则删除当前Token,重新添加
        $admin_token_model->delete($admin_id);
        //拼接数据
        $cond=[
          'admin_id'=>$admin_id,
          'token'=>\Org\Util\String::randString(40),
        ];
        //登陆的时候,勾选了保存登陆信息,将cookie保存一个星期
        auto_login($cond);
        return $admin_token_model->add($cond);
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
        $admin_token_model=M('AdminToken');
        if(!$admin_token_model->where($cookie)->count()){
            return false;
        }
        //为了防止被窃取,自动登陆一次后就重置token
        $this->_save_token($cookie['admin_id']);
        //自动登陆成功,并将用户信息保存到session
        $userinfo=$this->find($cookie['admin_id']);
        login($userinfo);
        //获取并保存用户权限
        $this->getPermissions($userinfo['id']);
        return $userinfo;
    }
}