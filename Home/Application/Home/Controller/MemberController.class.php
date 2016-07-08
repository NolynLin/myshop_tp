<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 10:07
 */

namespace Home\Controller;


use Think\Controller;

class MemberController extends Controller
{
    /**
     * @var \Home\Model\MemberModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Member');
        $meta_titles=[
            'reg'=>'用户注册',
            'login'=>'用户登录',
        ];
        $meta_title=(isset($meta_titles[ACTION_NAME])?$meta_titles[ACTION_NAME]:'用户登录');
        $this->assign('meta_title',$meta_title);
    }

    /**
     * 用户注册
     */
    public function reg()
    {
        if(IS_POST){
            if($this->_model->create('','reg')===false){

                $this->error(getError($this->_model));
            }
            if($this->_model->addMember()===false){
                $this->error(getError($this->_model));
            }
            $this->success('注册成功',U('index'));
        }else{
            $this->display('reg');
        }
    }

    /**
     * 激活账号
     * @param $email
     * @param $register_token
     */
    public function checkEmail($email,$register_token)
    {
        $cond=[
            'email'=>$email,
            'register_token'=>$register_token,
            'status'=>0,
        ];
        //如果能查出数据,则表示是当前用户操作
        if($this->_model->where($cond)->count()){
            //改变用户状态,
            $this->_model->where($cond)->setField('status',1);
            $this->success('激活成功',U('Member/index'));
        }else{
            $this->error('验证失败',U('Member/index'));
        }
    }
    //通过ajax验证用户名和邮箱是否重复
    public function checkByPram()
    {
        $cond=I('get.');
        if($this->_model->where($cond)->count()){
            $this->ajaxReturn(false);
        }else{
            $this->ajaxReturn(true);
        }
    }

    /**
     * 用户登陆
     */
    public function login()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->login()===false){
                $this->error(getError($this->_model));
            }
            $this->success('登陆成功',U('Index/index'));
        }else{
            $this->display();
        }
    }

    /**
     * 注销登陆
     */
    public function logout()
    {
        session(null);
        cookie(null);

        $this->success('退出成功',U('Index/index'));
    }

    /**
     * 用户登录信息展示
     */
    public function usertips()
    {
        $userinfo=login();
        if($userinfo['username']){
            $this->ajaxReturn($userinfo['username']);
        }else{
            $this->ajaxReturn(false);
        }
    }
}