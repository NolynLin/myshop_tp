<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 10:51
 */

namespace Admin\Model;


use Admin\Logic\DbMysqlLogic;
use Admin\Service\NestedSets;
use Think\Model;
use Think\Page;

class GoodsCategoryModel extends Model
{
    // 是否批处理验证
    protected $patchValidate    =   true;
    protected $_validate = [
        //验证分类名称
        ['name','require','商品分类名称不能为空'],
        //验证排序
        ['parent_id','require','所属分类不能为空'],
    ];
    public function getList()
    {
        return $this->where(['status'=>['egt',0]])->order('lft')->select();
    }

    /**
     * 添加商品
     * 使用nestedsets计算左右节点和层级
     */
    public function addCategory()
    {
//        getPk()删除主键
        unset($this->data[$this->getPk()]);
//        实例化nestedsets对象需要的数据库操作对象
        $mysql_db=D('DbMysql','Logic');
//        $this->trueTableName,tp中的属性,可以获取到表名
//        实例化nestedsets，并传入相关的字段
        $nestedsets=new NestedSets($mysql_db,$this->trueTableName,'lft','rght','parent_id','id','level');

        return $nestedsets->insert($this->data['parent_id'],$this->data,'bottom');
    }

    public function saveCategory()
    {
        //如果移动了父级分类,再去执行计算节点和层级的操作
        //1.获取原来的父级分类,不能用find去查找,这里要用动态查询,不能覆写data的内容
        $old_pid=$this->getFieldById($this->data['id'],'parent_id');
        //2.获取当前的父级分类
        $now_pid=$this->data['parent_id'];
        //3.比较
        if($old_pid!=$now_pid){
//        实例化nestedsets对象需要的数据库操作对象
        $orm=D('DbMysql','Logic');
//        $this->trueTableName,tp中的属性,可以获取到表名
//        实例化nestedsets，并传入相关的字段
        $nestedsets=new NestedSets($orm,$this->trueTableName,'lft','rght','parent_id','id','level');
        //moveUnder,只计算左右节点和层级,不保存其他数据,所以还要执行save
        if($nestedsets->moveUnder($this->data['id'],$this->data['parent_id'],'bottom')===false){
            $this->error='不能将分类移动到后代分类下';
            return false;
        }
        };
        $this->save();
    }
    public function removeCategory($id)
    {
        $pid=$this->getField('parent_id',true);
       if(in_array($id,$pid)){
           $this->error='当前分类存在子分类';
           return false;
       }
        //        实例化nestedsets对象需要的数据库操作对象
        $orm=D('DbMysql','Logic');
//        $this->trueTableName,tp中的属性,可以获取到表名
//        实例化nestedsets，并传入相关的字段
        $nestedsets=new NestedSets($orm,$this->trueTableName,'lft','rght','parent_id','id','level');
        return $nestedsets->delete($id);
    }
}