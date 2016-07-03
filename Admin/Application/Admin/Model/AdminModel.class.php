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
      ['username','','管理员名称不能重复',self::EXISTS_VALIDATE,'unique'],
      ['password','require','密码不能为空'],
      ['repassword','password','两次密码不一致',self::EXISTS_VALIDATE,'confirm'],
      ['password','6,16','密码长度不合法',self::EXISTS_VALIDATE,'length'],
      ['email','require','邮箱必填'],
      ['email','email','邮箱格式不合法'],
      ['email','','邮箱已存在',self::EXISTS_VALIDATE,'unique'],
    ];
    protected $_auto =[
      ['add_time',NOW_TIME,self::MODEL_INSERT],
      ['salt','\Org\Util\String::randString',self::MODEL_INSERT,'function'],
    ];
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
}