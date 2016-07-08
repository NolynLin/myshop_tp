<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/8
 * Time: 17:29
 */

namespace Home\Controller;


use Think\Controller;

class CartController extends Controller
{
    public function add2cart($id,$amount)
    {
        $userinfo=login();
        if(!$userinfo){
            //未登录,将商品信息放在cookie中
            $key='MEMBER_SHOPPING_CAR';
            //获取cookie中的数据,以商品的id为键,数量为值
            $cookie_goods=cookie($key);
            //判断当前是否存在商品数量
            if(isset($cookie_goods[$id])){
                $cookie_goods[$id]+=$amount;
            }else{
                $cookie_goods[$id]=$amount;
            }
            //将最新数据保存起来
            cookie($key,$cookie_goods,604800);
        }else{
        //已登录,将cookie中的数据保存到数据库,
            $shopping_car_model=D('ShoppingCar');
            $goods_count=$shopping_car_model->getGoodsCount($id,$userinfo['id']);
            //如果存在数量,
            if($goods_count){
                $shopping_car_model->saveGoodsCount($id,$amount,$userinfo['id']);
            }else{
                $shopping_car_model->addGoodsCount($id,$amount,$userinfo['id']);
            }
        }
        $this->success('添加成功',U('flow1'));
    }

    public function Cart()
    {
        $this->display();
    }
}