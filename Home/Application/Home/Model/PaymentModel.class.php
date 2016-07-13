<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/11
 * Time: 13:05
 */

namespace Home\Model;


use Think\Model;

class PaymentModel extends Model
{
    /**
     * 获取支付方式列表
     * @return mixed
     */
    public function getPaymentList()
    {
        return $this->where('status=1')->order('sort')->select();
    }

    /**
     * 通过id获取支付方式的name
     * @param $id
     * @param string $filed
     * @return mixed
     */
    public function getPaymentById($id,$filed='*')
    {
        $cond=[
            'id'=>$id,
            'status'=>1
        ];
        return $this->where($cond)->field($filed)->find();
    }
}