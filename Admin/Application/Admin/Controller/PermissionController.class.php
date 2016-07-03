<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 13:03
 */

namespace Admin\Controller;


use Think\Controller;

class PermissionController extends Controller
{
    /**
     * @var \Admin\Model\PermissionModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Permission');
    }

    /**
     * 展示权限列表,使用treegrid展示
     */

    public function index()
    {
        $permissions=$this->_model->getList();
        $this->assign('permissions',$permissions);
        $this->display();
    }
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->savePermission()===false){
                $this->error(getError($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
          //回显权限列表
        $this->_before_view();
         //回显权限信息
         $row=$this->_model->find($id);
         $this->assign('row',$row);
         $this->display('add');
        }
    }

    /**
     * 添加
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->addPermission()===false){
                $this->error(getError($this->_model));
            }
            $this->success('添加成功',U('index'));
        }else{
            //回显权限列表
            $this->_before_view();
            $this->display();
        }
    }
    public function remove($id)
    {
        if($this->_model->removePermission($id)===false){
            $this->error(getError($this->_model));
        }
        $this->success('删除成功',U('index'));
    }

    /**
     * 回显权限列表
     */
    private function _before_view()
    {
        $permissions=$this->_model->getList();
        array_unshift($permissions,['id'=>0,'name'=>'顶级分类','parent_id'=>0]);
        $this->assign('permissions',json_encode($permissions));
    }
}