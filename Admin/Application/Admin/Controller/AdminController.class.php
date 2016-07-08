<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/2
 * Time: 10:09
 */

namespace Admin\Controller;


use Think\Controller;

class AdminController extends Controller
{
    /**
     * @var \Admin\Model\AdminModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Admin');
    }

    /**
     * 获取管理员列表,分页数据
     */
    public function index()
    {
        $name=I('get.name');
        $cond=[];
        if($name){
            $cond['username'] = ['like', '%' . $name . '%'];
        }
        $rows=$this->_model->getPageAdmin($cond);
        $this->assign($rows);
        $this->display();
    }

    /**
     * 添加管理员
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create('','register')===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->addAdmin()===false){
                $this->error(getError($this->_model));
            }
            $this->success('添加管理员成功',U('index'));
        }else{
           $this->_before_view();
            $this->display();
        }
    }

    /**
     * 编辑管理员
     * @param $id
     */
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->saveAdmin($id)===false){
                $this->error(getError($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            //获取角色数据
            $this->_before_view();
            //回显管理员信息
            $row=$this->_model->getAdminInfo($id);
            $this->assign('row',$row);
            $this->display('add');
        }
    }

    /**
     * 删除管理员,同时删除对应权限
     * @param $id
     */
    public function remove($id)
    {
        if($this->_model->removeAdmin($id)===false){
            $this->error(getError($this->_model));
        }
        $this->success('删除成功',U('index'));
    }

    /**
     * 获取管理员数据
     */
    public function _before_view()
    {
        //获取角色数据
        $role_model=D('Role');
        $roles=$role_model->getList();
        $this->assign('roles',json_encode($roles));
    }

    public function repassword($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if(($new_pwd=$this->_model->repasswordAdmin())===false){
                $this->error(getError($this->_model));
            }
            //有没有一种js的弹出方式??告知密码后点击确定再跳转???
            $this->success('修改密码成功,您的新密码是,'.$new_pwd.',请妥善保管',U('index'),5);
        }else{
            //获取角色数据
            $this->_before_view();
            //回显管理员信息
            $row=$this->_model->getAdminInfo($id);
            $this->assign('row',$row);
            $this->display('resetpwd');
        }
    }

    /**
     * 登陆
     */
    public function login()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->adminLogin()===false){
                $this->error(getError($this->_model));
            }
            $this->success('登陆成功',U('Index/index'));
        }else{
            $this->display();
        }
    }

    /**
     * 退出
     */
    public function logout()
    {
        cookie(null);
        session(null);
        $this->success('退出成功',U('Admin/login'));
    }
}