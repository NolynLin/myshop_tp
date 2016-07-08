<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/2
 * Time: 15:18
 */

namespace Admin\Model;


use Admin\Service\NestedSets;
use Think\Model;

class MenuModel extends Model
{
    /**
     * 添加菜单
     * 1.将数据添加到菜单表
     * 2.根据返回的id,去添加菜单-权限关系表
     */
    public function addMenu()
    {
        unset($this->data['id']);
        $this->startTrans();
        //添加数据到菜单,由于使用的嵌套集合,所以使用通过nestedsets计算层级
        $mylogic_db=D('DbMysql','Logic');
        $nestedsets=new NestedSets($mylogic_db,$this->trueTableName,'lft','rght','parent_id','id','level');
        if(($menu_id=$nestedsets->insert($this->data['parent_id'],$this->data,'bottom'))===false){
            $this->error='添加菜单失败';
            $this->rollback();
            return false;
        }
        //根据返回的菜单id,添加对应的权限,一对多,这里的代码和修改类似,封装成一个方法
        if($this->_addPermisionByMenuId($menu_id)===false){
            $this->error='添加菜单-权限关联失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;

    }

    /**
     *
     * @return mixed
     */
    public function getList()
    {
        return $this->where(['status'=>1])->order('lft')->select();
    }

    /**
     * 查询菜单信息
     * 查询菜单所拥有的权限id
     * @param $id
     * @return mixed
     */
    public function getMenuInfo($id)
    {
        $row=$this->find($id);
        $menu_permission_model=M('MenuPermission');
        $row['permission_ids']=json_encode($menu_permission_model->where(['menu_id'=>$id])->getField('permission_id',true));
        return $row;
    }

    /**
     * @param $id
     * @return bool
     */
    public function saveMenu($id)
    {
        $this->startTrans();
        //修改菜单基本信息,先判定父级有没有改变,有改变就用nestedsets重新计算层级,
        //没有就不用管,
        $old_pid=$this->where(['id'=>$id])->getField('parent_id');
        if($old_pid!=$this->data['parent_id']){
            $myDbLogic=D('DbMysql','Logic');
            $nestdsets=new NestedSets($myDbLogic,$this->trueTableName,'lft','rght','parent_id','id','level');
            if($nestdsets->moveUnder($id,$this->data['parent_id'],'bottom')===false){
                $this->error='不能将分类移动到自身或后代分类中';
                $this->rollback();
                return false;
            }
        }
        //保存其他数据
        if($this->save()===false){
            $this->error='修改菜单失败';
            $this->rollback();
            return false;
        }
        //删除菜单原有权限
        $menu_permission_model=M('MenuPermission');
        if($menu_permission_model->where(['menu_id'=>$id])->delete()===false){
            $this->rollback();
            return false;
        };
        //新添加权限
        if($this->_addPermisionByMenuId($id)===false){
            $this->error='添加菜单-权限关联失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 根据菜单id添加权限id到菜单-权限关系表,
     * @param $menu_id
     * @return bool|string
     */
    protected function _addPermisionByMenuId($menu_id)
    {
        $permission_ids=I('post.permission_id');
        $data=[];
        $menu_permission_model=M('MenuPermission');
        //存在权限id才去做添加
        if($permission_ids){
            foreach($permission_ids as $permissionid){
                $data[]= ['menu_id'=>$menu_id,'permission_id'=>$permissionid];
            }
           return  $menu_permission_model->addAll($data);
        }else{
            return true;
        }
    }

    /**
     * 删除当前菜单
     * 删除当前菜单在中间表对应的权限
     * 如果当前菜单有子菜单被删除,那么也要在中间表删除子菜单对应的权限
     * @param $id
     * @return bool
     */
    public function removeMenu($id)
    {
        $this->startTrans();
        //根据左右节点判断当前菜单是否有子菜单
        $permission_info=$this->field('lft,rght')->find($id);
        //大于左节点并且小于右节点的,都是子菜单
        $cond=[
            'lft'=>['egt',$permission_info['lft']],
            'rght'=>['elt',$permission_info['rght']],
        ];
        //获取子权限的id,也包括了当前菜单本身
        $permission_child_ids=$this->where($cond)->getField('id',true);
        //删除菜单-权限关联记录
        $menu_permission_model=M('MenuPermission');
        if($menu_permission_model->where(['menu_id'=>['in',$permission_child_ids]])->delete()===false){
            $this->error='删除菜单-权限关联失败';
            $this->rollback();
            return false;
        }
        //删除菜单的同时需要重新计算层级,所以用nestedsets
        $myDbLogic=D('DbMysql','Logic');
        $nestedsets=new NestedSets($myDbLogic,$this->trueTableName,'lft','rght','parent_id','id','level');
        if($nestedsets->delete($id)===false){
            $this->error='删除菜单失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 获取可见菜单
     * @return array
     */
    public function getMenuList()
    {
        //如果是超级管理员,那么所有菜单可见
        if(login()['username']=='admin'){
            $admin_menus=$this->alias('m')->distinct(true)->field('path,id,name,parent_id')->join('__MENU_PERMISSION__ as mp on mp.menu_id=m.id')->select();
        }else{
        //获取用户权限id
        $admin_permissionids=permission_pids();
        //获取用户菜单id,根据权限id,去查询,这里用in表达式,
        if($admin_permissionids){
            $admin_menus=$this->alias('m')->distinct(true)->field('path,id,name,parent_id')->join('__MENU_PERMISSION__ as mp on mp.menu_id=m.id')->where(['permission_id'=>['in',$admin_permissionids]])->select();
        }else{
            $admin_menus=[];
        }
        }
        return $admin_menus;
        //获取菜单信息
    }
}