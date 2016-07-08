<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/8
 * Time: 12:37
 */

namespace Home\Controller;


use Think\Controller;
use Think\Session\Driver\Redis;

class GoodsController extends Controller
{
    /**
     *获取商品浏览次数
     * @param $goods_id
     */
    public function getClick($goods_id)
    {
        $goods_click_model=M('GoodsClick');
        $num=$goods_click_model->getFieldByGoodsId($goods_id,'click_times');
        if(!$num){
            $num=1;
            $cond=[
                'goods_id'=>$goods_id,
                'click_times'=>$num,
            ];
            $goods_click_model->add($cond);
        }else{
            ++$num;
            $cond=[
                'goods_id'=>$goods_id,
                'click_times'=>$num,
            ];
            $goods_click_model->save($cond);
        }
        $this->ajaxReturn($num);
    }

    /**
     * 将点击次数存入redis
     */
    public function getClickByRedis($goods_id)
    {
        //取得redis实例
        $redis=get_redis();
        //设置键
        $key='goods_click';
        //设置自加一,并返回
        $this->ajaxReturn($redis->zIncrBy($key,1,$goods_id));
    }

    /**
     * 将redis的商品浏览次数数据保存到数据库
     */
    public function saveClickTimes()
    {
        //取得redis实例
        $redis=get_redis();
        //取得键
        $key='goods_click';
        //获取当前键的数据,获取的格式是数组,商品id号为键,点击次数为值
        $data=$redis->zRange($key,0,-1,true);
        //如果没数据,直接返回
        if(empty($data)){
            return true;
        }
        //如果有很多数据,则分段插入,
        //$temp=array_chunk($data,1000,true);
        //如果有数据,那么获取到商品id,将数据库已存在的这些对应商品id的数据删除,再添加
        $goodsids=array_keys($data);
        $goods_click_model=M('GoodsClick');
        $goods_click_model->where(['goods_id'=>['in',$goodsids]])->delete();
        //添加所有redis到数据库,
        $data=[];
        foreach ($goodsids as $key=>$val) {
            $data[]=[
                'goods_id' =>$key,
                'click_times'=>$val
            ];
        }
       return $goods_click_model->addAll($data);
    }

}