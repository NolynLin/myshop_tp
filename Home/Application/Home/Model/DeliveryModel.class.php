<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/11
 * Time: 12:33
 */

namespace Home\Model;


use Think\Model;

class DeliveryModel extends Model
{
    /**
     * @return mixed
     */
    public function getDeliveryList()
    {
        return $this->where('status=1')->order('sort')->select();
    }

    /**
     * 通过配送方式id,获取指定字段
     * @param $id
     * @param string $field
     * @return array
     */
    public function getDeliveryById($id,$field='*')
    {
        $cond=[
            'id'=>$id,
            'status'=>1
        ];
        return $this->where($cond)->field($field)->find();
    }
}