<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/13
 * Time: 20:59
 */

namespace Admin\Model;


use Think\Model;

class OrderInfoModel extends Model
{
    public $statues = [
        0=>'已取消',
        1=>'待支付',
        2=>'待发货',
        3=>'待收货',
        4=>'完成',
    ];

    /**
     * 获取订单详情,按照添加时间排序
     * @return mixed
     */
    public function getList()
    {
        return $this->order('inputtime desc')->select();
    }
}