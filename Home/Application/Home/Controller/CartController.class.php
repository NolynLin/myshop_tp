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
    /**
     * @var \Home\Model\ShoppingCarModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('ShoppingCar');
    }
    /**
     * 添加商品,分登陆和未登录
     * @param $id      商品id
     * @param $amount  商品数量
     */
    public function add2cart($id,$amount)
    {
        $userinfo=login();
        if(!$userinfo){
            //未登录,将商品信息放在cookie中
            $key=C('COOKIE_SHOPPING_CAR');
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
        //已登录,直接将数据保存到数据库,先查看当前用户购物车是否已经有这个商品
            $goods_count=$this->_model->getGoodsCount($id,$userinfo['id']);
            //如果存在数量,
            if($goods_count){
                $this->_model->saveGoodsCount($id,$amount,$userinfo['id']);
            }else{
                $this->_model->addGoodsCount($id,$amount,$userinfo['id']);
            }
        }
        $this->success('添加成功',U('flow1'));
    }

    /**
     * 添加成功后执行的方法.
     * 1.登陆
     * 1.1 从数据库获取购物车的商品id,数量amount
     * 1.2根据商品id去查找商品详情,logo,name,shop_price,
     * 1.3展示详情
     * 2.未登录
     * 2.1 从cookie获取购物车的商品id,数量amount
     * 2.2根据商品id去查找商品详情,logo,name,shop_price,
     * 2.3展示详情
     */
    public function flow1()
    {
        //获取商品的详情
        $shopping_car_goods_info=$this->_model->getShoppingCarGoodsList();
        $this->assign($shopping_car_goods_info);
        $this->display();
    }

    /**
     * 展示结算页面
     * 当点击结算的时候,如果没有登录,则跳到登录页面
     * 登录了就正常执行
     */
    public function flow2()
    {
        $userinfo=login();
        if(!$userinfo){
            //将当前页面保存到cookie中,以便登录后继续跳回结算页面,__SELF__获取当前url地址
            cookie('__FORWARD__',__SELF__);
            $this->error('本店不招待无名之辈,报上名来',U('Member/login'));
        }
        //展示收货人地址信息列表
        //展示当前用户的所有收货地址
        $addresses_model=D('Address');
        $addresses=$addresses_model->getAddressList();
        $this->assign('addresses',$addresses);
        //展示送货方式列表
        $delivery_model=D('Delivery');
        $deliveries=$delivery_model->getDeliveryList();
        $this->assign('deliveries',$deliveries);
        //展示支付方式列表
        $payment_model=D('Payment');
        $payments=$payment_model->getPaymentList();
        $this->assign('payments',$payments);
        //展示发票信息列表
        //展示商品清单列表
        $this->assign($this->_model->getShoppingCarGoodsList());
        $this->display();
    }

    /**
     * 订单提交成功
     */
    public function flow3()
    {
        $this->display();
    }

    /**
     * 删除购物车
     * @param $id
     */
    public function remove($id)
    {
        $this->ajaxReturn($this->_model->removeGoods($id));
    }
}