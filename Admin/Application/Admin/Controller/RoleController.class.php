<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 17:28
 */

namespace Admin\Controller;


use Think\Controller;

class RoleController extends  Controller
{
    /**
     * @var \Admin\Model\RoleModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Role');
    }

    public function index()
    {
        //搜索条件
        $name=I('get.name');
        $cond = [];
        if ($name) {
            $cond['name'] = [
                'like', '%' . $name . '%'
            ];
        }
        $rows=$this->_model->getRolesPage($cond);
        $this->assign($rows);
        $this->display();
    }
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->saveRole()===false){
                $this->error(getError($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            //回显角色信息,和对应的权限
            $row=$this->_model->getRoleInfo($id);
            $this->assign('row',$row);
            //回显展示可选的权限
            $this->_before_view();
            $this->display('add');
        }
    }

    /**
     * 删除角色,同时删除角色对应拥有的权限
     * @param $id
     */
    public function remove($id)
    {
        if($this->_model->removeRole($id)===false){
            $this->error(getError($this->_model));
        }
        $this->success('删除成功',U('index'));
    }
    public function add()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->addRole()===false){
                $this->error(getError($this->_model));
            }
            $this->success('添加成功',U('index'));
        }else{
           $this->_before_view();
           $this->display();
        }

    }
    private function _before_view(){
        //显示权限列表
        $permissionModel=D('Permission');
        $permissions=$permissionModel->getList();
        $this->assign('permissions',json_encode($permissions));
    }
}