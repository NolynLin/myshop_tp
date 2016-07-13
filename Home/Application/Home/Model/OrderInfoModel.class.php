<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/11
 * Time: 17:16
 */

namespace Home\Model;


use Think\Model;

class OrderInfoModel extends Model
{
    /**
     * 添加订单
     * 1.保存订单基本信息
     * 2.根据生成的订单id,保存订单详情
     * 3.获取发票信息并保存
     * 4.清空购物车
     * @return bool
     */

    public function addOrder()
    {
        $this->startTrans();
        //1.添加订单基本信息
        //1.1通过收货地址id,去获取地址信息
        $address_id=I('post.address_id');
        $address_model=D('Address');
        $address_info=$address_model->getListById($address_id,'name,province_name,city_name,area_name,detail_address,tel,member_id');
        $this->data=array_merge($this->data,$address_info);
        //1.2通过配送方式id,获取配送方式的name和价格
        $delivery_model=D('Delivery');
        $deliveries_info=$delivery_model->getDeliveryById($this->data['delivery_id'],'name as delivery_name,price as delivery_price');
        $this->data=array_merge($this->data,$deliveries_info);
        //1.3通过支付方式id,去查询支付名称
        $payment_model=D('Payment');
        $payments=$payment_model->getPaymentById(I('post.pay_type'),'name as pay_type_name');
        $this->data=array_merge($this->data,$payments);
        //1.4获取商品总金额
        $shopping_car_model=D('ShoppingCar');
        $shopping_car_info=$shopping_car_model->getShoppingCarGoodsList();
        $this->data['price']=$shopping_car_info['total_price'];
        $this->data['status']=1;//订单创建时默认待支付
        //1.5保存订单
        if(($order_id=$this->add())===false){
            $this->error='订单信息保存失败';
            $this->rollback();
            return false;
        }
        //2.保存订单明细
        if($this->_save_order_info_item($shopping_car_info,$order_id)===false){
            $this->error='保存订单明细失败';
            $this->rollback();
            return false;
        }
        //3.添加发票信息
        if($this->_save_invoice($address_info,$shopping_car_info,$order_id)===false){
            $this->error='保存发票信息失败';
            $this->rollback();
            return false;
        }
        //成功以后删除清空用户购物车
        if($shopping_car_model->clearCarByMemberId()===false){
            $this->error=$shopping_car_model->getError();
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }
    protected function _save_invoice($address_info,$shopping_car_info,$order_id){
        //3.1获取发票抬头
        $receipt_type=I('post.type');
        //个人,获取用户姓名
        if($receipt_type==1){
            $receipt_title=$address_info['name'];
        }else{
            //企业,获取输入的企业名称
            $receipt_title=I('post.company_name');
        }
        /**
         * 发票详细内容的获取,分四种情况
         * 1.明细
         * 王老五
         * 酸奶机  199.00 × 1  199.00
         * 奶牛    5000.00 × 1 5000.00
         *
         * 总计：5199.00
         */
        $receipt_content='';
        switch(I('post.receipt_content')){
            case 1:
                $tmp=[];
                foreach($shopping_car_info['goods_info'] as $goods){
                    $tmp[]=$goods['name']."\t".$goods['price'].'×'.$goods['amount']."\t".$goods['stotal_price'];
                }
                $receipt_content=implode("\r\n",$tmp);
                break;
            case 2:
                $receipt_content='办公用品';
                break;
            case 3:
                $receipt_content='体育休闲';
                break;
            case 4:
                $receipt_content='耗材';
            default;
        }
        //发票内容为详细加总金额
        $content = $receipt_title . "\r\n" . $receipt_content . "\r\n总计：" . $shopping_car_info['total_price'];
        //整理发票数据
        foreach($shopping_car_info['goods_info'] as $goods){
            $data=[
                'name'=>$receipt_title,
                'content'=>$content,
                'price'=>$shopping_car_info['total_price'],
                'inputtime'=>NOW_TIME,
                'member_id'=>$address_info['member_id'],
                'order_info_id'=>$order_id
            ];
        }
        //创建模型,保存发票
        $invoice_model=M('Invoice');
        return $invoice_model->add($data);
    }

    /**
     * 保存订单详情
     * @param $shopping_car_info
     * @param $order_id
     * @return bool|string
     */
    protected function _save_order_info_item($shopping_car_info,$order_id){
        //2.1获取购物车数据
        $data=[];
        //拼接数据
        foreach($shopping_car_info['goods_info'] as $goods){
            $data[]=[
                'order_info_id'=>$order_id,
                'goods_id'=>$goods['id'],
                'goods_name'=>$goods['name'],
                'logo'=>$goods['logo'],
                'total_price'=>$goods['stotal_price'],
                'amount'=>$goods['amount']
            ];
        }
        //2.2添加数据到订单详细表
        $order_info_model=M('OrderInfoItem');
        return $order_info_model->addAll($data);
    }
}