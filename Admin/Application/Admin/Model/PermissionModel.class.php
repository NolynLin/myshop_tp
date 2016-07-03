<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 13:05
 */

namespace Admin\Model;


use Admin\Service\NestedSets;
use Think\Model;

class PermissionModel extends Model
{
    /**
     * 添加权限，通过nestedsets计算层级，左右节点
     * @return false|int
     */
    public function addPermission()
    {
        unset($this->data['id']);
        $mylogic=D('DbMysql','Logic');
        $nestedsets=new NestedSets($mylogic,$this->trueTableName,'lft','rght','parent_id','id','level');
        return $nestedsets->insert($this->data['parent_id'],$this->data,'bottom');
    }

    /**
     * 查询满足条件的权限
     * @return mixed
     */

    public function getList()
    {
        return $this->where(['status'=>'1'])->order('lft')->select();
    }

    /**
     * 修改权限信息
     */
    public function savePermission()
    {
        //判定是否修改了父级分类
        $old_pid=$this->getFieldById($this->data['id'],'parent_id');

        if($old_pid!=$this->data['parent_id']){
            $mylogic=D('DbMysql','Logic');
            $nestedsets=new NestedSets($mylogic,$this->trueTableName,'lft','rght','parent_id','id','level');
            //这里只会重新计算左右节点和层级,保存其他数据还是要在最后
            if($nestedsets->moveUnder($this->data['id'],$this->data['parent_id'],'bottom')===false){
                $this->error= '不能将分类移动到自身或后代分类中';
                return false;
            }
        }
        //保存其他数据
        return $this->save();
    }

    /**
     * 删除权限,重新计算左右节点和层级数据,并且删除角色所拥有的当前权限
     * @param $id
     * @return bool
     */

    public function removePermission($id)
    {
        $this->startTrans();
        //删除角色所拥有的当前权限，如果当前权限拥有子权限，也要将子权限一起删除
        //获取后代权限
        $permission_info = $this->field('lft,rght')->find($id);
        $cond = [
            'lft'=>['egt',$permission_info['lft']],
            'rght'=>['elt',$permission_info['rght']],
        ];
        $permission_ids = $this->where($cond)->getField('id',true);
        //删除角色-权限中间表的相关权限记录
        $role_permission_model = M('RolePermission');
        if($role_permission_model->where(['permission_id'=>['in',$permission_ids]])->delete()===false){
            $this->error = '删除角色-权限关联失败';
            $this->rollback();
            return false;
        }
        //删除权限
        $mylogic=D('DbMysql','Logic');
        $nestedsets=new NestedSets($mylogic,$this->trueTableName,'lft','rght','parent_id','id','level');
        if($nestedsets->delete($id)===false){
            $this->error='删除权限失败';
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;

    }
}