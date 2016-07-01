<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 17:29
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class RoleModel extends Model
{
    /**
     * 添加角色,将基本信息添加到角色表,
     * 2.将对应的权限id绑定给角色
     */
    public function addRole()
    {
        $this->startTrans();
        //1.添加角色基本信息
        if(($role_id=$this->add())===false){
            $this->rollback();
            return false;
        }
        //2.绑定对应权限
        //获取选择的权限
        $permissions=I('post.permission_id');
        //拼接data数据
        $data=[];
        //选择了权限才去执行添加
        if($permissions){
            foreach($permissions as $v){
                $data[]=['role_id'=>$role_id,'permission_id'=>$v];
            }
            $role_permission_model=M('RolePermission');
            if($role_permission_model->addAll($data)===false){
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     *
     * 获取角色分页数据
     * @param array $cond
     * @return array
     */

    public function getRolesPage(array $cond=[])
    {
        //查询条件
        $cond=array_merge(['status'=>1],$cond);
        //总行数
        $count=$this->where($cond)->count();
        //获取分页配置
        $page_setting=C('PAGE_SETTING');
        $page=new Page($count,$page_setting['PAGE_SIZE']);
        //设置分页样式
        $page->setConfig('theme',$page_setting['PAGE_THEME']);
        $page_html=$page->show();
        $rows=$this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('rows','page_html');
    }

    /**
     * 回显角色信息得方法
     * @param $id
     * @return mixed
     */
    public function getRoleInfo($id)
    {
        //获取角色基本信息
        $row=$this->find($id);
        $role_permission_model=M('RolePermission');
        //获取角色对应的权限id
        $permissionId=$role_permission_model->where(['role_id'=>$id])->getField('permission_id',true);
        //将角色对应的权限id转成json格式传过去
        $row['permission_ids']=json_encode($permissionId);
       return $row;
    }

    /**
     * 保存角色基本信息,并且删除旧的角色权限,添加新的角色权限
     * @return bool
     */
    public function saveRole()
    {
        $id=$this->data['id'];
        $this->startTrans();
        //1.保存角色基本信息
        if($this->save()===false){
            $this->rollback();
            return false;
        }
        //2.保存角色权限

        $permissions=I('post.permission_id');
        //2.1删除角色当前权限,
        $role_permission_model=M('RolePermission');
        if($role_permission_model->where(['role_id'=>$id])->delete()===false){
            $this->error='删除历史权限失败';
            $this->rollback();
            return false;
        }
        //2.2添加新的权限
        if($permissions){
            $data=[];
            foreach($permissions as $val){
                $data[]=['role_id'=>$id,'permission_id'=>$val];
            }
            if($role_permission_model->addAll($data)===false){
                $this->error = '保存权限失败';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    public function removeRole($id)
    {
        $this->startTrans();
        //删除角色信息
        if($this->delete($id)===false){
            $this->error ='删除角色信息失败';
            $this->rollback();
            return false;
        }
        //删除角色当前权限,
        $role_permission_model=M('RolePermission');
        if($role_permission_model->where(['role_id'=>$id])->delete()===false){
            $this->error='删除历史权限失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

}