<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/7
 * Time: 15:53
 */

namespace Home\Model;


use Think\Model;

class GoodsCategoryModel extends Model
{
    /**
     * 根据字段获取商品分类列表
     * @param string $filed
     * @return mixed
     */
    public function getList($filed='*')
    {
        return $this->field($filed)->where(['status'=>1])->select();
    }

}