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
    public function add()
    {
        if(IS_POST){
            if($this->_model->create()===false){
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

    public function _before_view()
    {
        //获取角色数据
        $role_model=D('Role');
        $roles=$role_model->getList();
        $this->assign('roles',json_encode($roles));
    }
}