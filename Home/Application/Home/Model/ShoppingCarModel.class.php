<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/8
 * Time: 18:00
 */

namespace Home\Model;


use Think\Model;

class ShoppingCarModel extends Model
{
    /**
     * 获取当前用户的商品在数据库的数量
     * @param $goods_id
     * @param $userId
     * @return mixed
     */
    public function getGoodsCount($goods_id,$userId)
    {
        $cond=[
            'goods_id'=>$goods_id,
            'member_id'=>$userId
        ];
        return $this->where($cond)->getField('amount');
    }

    /**
     * 数据库存在当前商品数量,则加上传过来的数量,再保存总数量
     * @param $goods_id    商品id
     * @param $count       购买的数量
     * @param $userId      当前登录用户id
     * @return boolean
     */
    public function saveGoodsCount($goods_id,$count,$userId)
    {
        $cond=[
            'goods_id'=>$goods_id,
            'member_id'=>$userId
        ];
       return  $this->where($cond)->setInc('amount',$count);
    }

    /**
     * 没有当前商品数量的时候,直接添加
     * @param $goods_id  商品id
     * @param $count     购买的数量
     * @param $userId    当前登录用户id
     * @return mixed
     */
    public function addGoodsCount($goods_id,$count,$userId)
    {
        $data=[
            'amount'=>$count,
            'goods_id'=>$goods_id,
            'member_id'=>$userId
        ];
        return $this->add($data);
    }
}