<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 10:51
 */

namespace Admin\Model;


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
}