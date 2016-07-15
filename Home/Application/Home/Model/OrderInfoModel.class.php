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
    public $statues=[
        0=>'取消',
        1=>'待付款',
        2=>'待发货',
        3=>'待收货',
        4=>'交易完成'
    ];
    /**
     * 添加订单
     * 1.保存订单基本信息
     * 2.根据生成的订单id,保存订单详情
     * 3.获取发票信息并保存
     * 4.清空购物车
     * 5.判断库存
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
        $this->data['inputtime']=NOW_TIME;//订单创建时默认待支付
        //5判断库存
        $cond['_logic']='OR';
        //循环拼接条件,当前商品的id,并且库存小于购物车数量,
        //如果能取到数据,说明购物车数量大于库存,不让买,并且给出提示
        //如果取不到数据,说明库存大于购物车,可以买
        foreach($shopping_car_info['goods_info'] as $goods_id=>$goods){
            $cond[]=[
                'id'=>$goods_id,
                'stock'=>['lt',$goods['amount']]
            ];
        }
        $goods_model=M('Goods');
        $error='';
        //查询数据
        $goodses=$goods_model->where($cond)->select();
        //sql语句最后转成了SELECT * FROM `goods` WHERE (  `id` = 12 AND `stock` < '3' ) OR (  `id` = 13 AND `stock` < '1' )
        //取到数据,说明购物车数量大于库存,不让买,并且给出提示
        if($goodses){
            foreach($goodses as $goods){
                $error.=$goods['name'].'  ';
            }
            $this->error=$error.'库存不足';
            return false;
        }
        //5.1保存订单之后,减去Goods表中的库存
        foreach($shopping_car_info['goods_info'] as $goods_id=>$goods){
           if($goods_model->where(['id'=>$goods_id])->setDec('stock',$goods['amount'])===false){
               $this->error='更新库存失败';
               $this->rollback();
               return false;
           }
        }
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

    /**
     * 保存发票
     * @param $address_info
     * @param $shopping_car_info
     * @param $order_id
     * @return mixed
     */
    protected function _save_invoice($address_info,$shopping_car_info,$order_id){
        //3.1获取发票抬头
        $receipt_type=I('post.receipt_type');
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
                'price'=>$goods['shop_price'],
                'amount'=>$goods['amount']
            ];
        }
        //2.2添加数据到订单详细表
        $order_info_model=M('OrderInfoItem');
        return $order_info_model->addAll($data);
    }

    /**
     * 获取订单数据
     * @return mixed
     */
    public function getList()
    {
        //获取订单基本信息
        $userinfo=login();
        $rows=$this->where(['member_id'=>$userinfo['id']])->select();
        //获取订单详情,logo和商品名称
        $order_info_item_model=M('OrderInfoItem');
        foreach($rows as $key=>$row){
            $rows[$key]['goods_info']=$order_info_item_model->field('goods_id,logo,goods_name')->where(['order_info_id'=>$row['id']])->select();
        }
        return $rows;
    }

    /**
     * 通过订单id号,获取数据
     * @param $id
     * @return array
     */
    public function getListById($id)
    {
        return $this->find($id);
    }

    /**
     * 清除超时订单
     * @return bool
     */
    public function clearCarOutTime()
    {
        $this->startTrans();
        $cond=[
            'inputtime'=>[
              'lt',NOW_TIME-900
          ],
            'status'=>1
        ];
        //如果没有过期的订单,则什么也不做
        $outtime_car_id=$this->where($cond)->getField('id',true);
        if(!$outtime_car_id){
          return true;
        }
        //1.删除订单相关
        //1.1有,则删除订单
        if($this->where($cond)->setField(['status'=>0])===false){
            $this->error='改变超时订单状态失败';
            $this->rollback();
            return false;
        }
        //先从订单详情中获取商品id和对应的数量
        $goods_ids=M('OrderInfoItem')->where(['order_info_id'=>['in',$outtime_car_id]])->getField('id,goods_id,amount');
//        //1.2删除订单详情
//        if(M('OrderInfoItem')->where(['order_info_id'=>['in',$outtime_car_id]])->delete()===false){
//            $this->error='删除超时订单详情失败';
//            $this->rollback();
//            return false;
//        }
//        //1.3删除发票
//        if(M('Invoice')->where(['order_info_id'=>['in',$outtime_car_id]])->delete()===false){
//            $this->error='删除超时订单发票失败';
//            $this->rollback();
//            return false;
//        }
        //2.查找出商品数量,返还给商品库存
        $data=[];
        foreach($goods_ids as $goods){
            if(isset( $data[$goods['goods_id']])){
                $data[$goods['goods_id']]+=$goods['amount'];
            }else{
                $data[$goods['goods_id']]=$goods['amount'];
            }
        }
        foreach($data as $goods_id=>$amount){
            if(M('Goods')->where(['id'=>$goods_id])->setInc('stock',$amount)===false){
                $this->error='恢复商品库存失败';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;

    }

    /**
     * 用户删除订单
     * @param $id
     * @return bool
     */
    public function removeCar($id)
    {
        $this->startTrans();
        $cond=[
            'id'=>$id,
            'status'=>1
        ];
        //1.1有,则删除订单
        if($this->where($cond)->setField(['status'=>0])===false){
            $this->error='改变超时订单状态失败';
            $this->rollback();
            return false;
        }
        //先从订单详情中获取商品id和对应的数量
        $goods_ids=M('OrderInfoItem')->where(['order_info_id'=>$id])->getField('id,goods_id,amount');
//        //1.2删除订单详情
//        if(M('OrderInfoItem')->where(['order_info_id'=>['in',$outtime_car_id]])->delete()===false){
//            $this->error='删除超时订单详情失败';
//            $this->rollback();
//            return false;
//        }
//        //1.3删除发票
//        if(M('Invoice')->where(['order_info_id'=>['in',$outtime_car_id]])->delete()===false){
//            $this->error='删除超时订单发票失败';
//            $this->rollback();
//            return false;
//        }
        //2.查找出商品数量,返还给商品库存
        $data=[];
        foreach($goods_ids as $goods){
            if(isset( $data[$goods['goods_id']])){
                $data[$goods['goods_id']]+=$goods['amount'];
            }else{
                $data[$goods['goods_id']]=$goods['amount'];
            }
        }
        foreach($data as $goods_id=>$amount){
            if(M('Goods')->where(['id'=>$goods_id])->setInc('stock',$amount)===false){
                $this->error='恢复商品库存失败';
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }
}