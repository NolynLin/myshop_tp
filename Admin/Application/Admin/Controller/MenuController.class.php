<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/2
 * Time: 15:17
 */

namespace Admin\Controller;


use Think\Controller;

class MenuController extends Controller
{
    /**
     * @var \Admin\Model\MenuModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Menu');
    }

    public function index()
    {
        $rows=$this->_model->getList();
        $this->assign('rows',$rows);
        $this->display();
    }

    /**
     * 添加菜单
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->addMenu()===false){
                $this->error(getError($this->_model));
            }
            $this->success('添加成功',U('index'));
        }else{
            //显示菜单列表
            $this->_before_view();
            $this->display();
        }
    }

    /**
     * 修改菜单
     * 修改菜单基本信息
     * 修改菜单所属分类
     * 修改菜单权限
     * @param $id
     */
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->saveMenu($id)===false){
                $this->error(getError($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            //获取菜单信息
            $row=$this->_model->getMenuInfo($id);
            $this->assign('row',$row);
            //回显菜单列表,权限列表
            $this->_before_view();
            $this->display('add');
        }
    }

    /**
     * 删除当前菜单
     * 删除当前菜单在中间表对应的权限
     * 如果当前菜单有子菜单被删除,那么也要在中间表删除子菜单对应的权限
     * @param $id
     */
    public function remove($id)
    {
        if($this->_model->removeMenu($id)===false){
            $this->error(getError($this->_model));
        }
        $this->success('删除成功',U('index'));
    }
    protected function _before_view(){
        //获取菜单列表
        $menus=$this->_model->getList();
        array_unshift($menus,['id'=>0,'name'=>'顶级菜单','parent_id'=>0]);
        $this->assign('menus',json_encode($menus));
        //获取权限列表
        $permission_model=D('Permission');
        $permissions=$permission_model->getList();
        $this->assign('permissions',json_encode($permissions));
    }
}