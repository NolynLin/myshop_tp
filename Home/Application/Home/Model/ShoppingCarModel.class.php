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

    /**
     * 用户登陆后将cookie存的购物车商品保存到数据库
     * 1.获取cookie的商品信息,
     * 2.将数据库存在的相同商品删除
     * 3.添加新的购物车信息到数据库
     */
    public function addCookie2db($uerid)
    {
        $key=C('COOKIE_SHOPPING_CAR');
        //获取cookie的购物车信息
        $cookie_car=cookie($key);
        if(!$cookie_car){
            return true;
        }
        //将数据库存在的跟cookie相同的商品信息删除
            $cookie_car_ids=[
                'member_id'=>$uerid,
                'goods_id'=>['in',array_keys($cookie_car)]
            ];
        $this->where($cookie_car_ids)->delete();
        //保存新的购物车信息
        $date=[];
        foreach($cookie_car as $key=>$val){
            $date[]=[
                'member_id'=>$uerid,
                'goods_id'=>$key,
                'amount'=>$val
            ];
        }
        //清除cookie中的购物车信息
        cookie($key,null);
        return $this->addAll($date);
    }

    /**
     * 获取登陆用户的购物车信息
     * @param $userid
     * @return mixed
     */
    public function getShoppingCarInfo($userid)
    {
        return $this->where(['member_id'=>$userid])->getField('goods_id,amount');
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
     * * 获取用户购物车的商品详细信息
     * name,shop_price,logo
     */
    public function getShoppingCarGoodsList()
    {
        //用户登陆了,从数据库获取购物车的商品id,数量amount
        $userinfo=login();
        if($userinfo){
            $shoop_car_info=$this->getShoppingCarInfo($userinfo['id']);
        }else{
            //用户未登陆,从cookie获取购物车的商品id,数量amount
            $shoop_car_info=cookie(C('COOKIE_SHOPPING_CAR'));
        }
        //购物车有数据才执行查询,
        if(!$shoop_car_info){
             return ;
        }
        $goods_model=M('Goods');
        //获取商品的信息,以商品的id作为数组的键,这样可以根据键去给数组添加对应的字段
        $goods_info=$goods_model->where(['id'=>['in',array_keys($shoop_car_info)],'is_on_sale'=>1,'status'=>1])->getField('id,name,logo,shop_price');
        //计算好总价
        $total_price=0.00;
        $total_amount=0;
        //只有当登陆了才去获取积分,计算打折后的价格
//        $score=M('Member')->where('id=null')->getField('score');
        //获取用户积分
        $score=M('Member')->where(['id'=>$userinfo['id']])->getField('score');
        //获取用户级别
        $cond=[
          'bottom'=>['elt',$score],
          'top'=>['egt',$score],
        ];
        //获取级别id和打折率
        $level=M('MemberLevel')->where($cond)->field('id,discount')->find();
        //获取用户会员价
        $member_price_model=M('MemberGoodsPrice');
        //循环,将商品id对应的数据添加数量和小计字段
        foreach($shoop_car_info as $goods_id=>$amount){
            //查看会员价
            $member_price=$member_price_model->where(['goods_id'=>$goods_id,'member_level_id'=>$level['id']])->getField('price');
            //如果当前商品存在特殊的会员价,以数据库为准,没有则按照会员等级自动计算
            if($userinfo && $member_price){
                $goods_info[$goods_id]['shop_price']=locate_number_format($member_price);
            }elseif($userinfo){
                $goods_info[$goods_id]['shop_price']=locate_number_format($level['discount']*$goods_info[$goods_id]['shop_price']/100);
            }else{
                $goods_info[$goods_id]['shop_price']=locate_number_format( $goods_info[$goods_id]['shop_price']);
            }
            $goods_info[$goods_id]['amount']=$amount;
            //添加小计字段并转成100.00这种格式
            $goods_info[$goods_id]['stotal_price']=locate_number_format($goods_info[$goods_id]['shop_price'] * $amount);
            $total_price+=$goods_info[$goods_id]['stotal_price'];
            $total_amount+=$amount;
        }
        $total_price=locate_number_format($total_price);
        return compact('total_amount','goods_info','total_price');
    }

    /**
     * 购物车成功提交后,删除当前用户的购物车信息
     * @return mixed
     */
    public function clearCarByMemberId()
    {
        $userinfo=login();
        return $this->where(['member_id'=>$userinfo['id']])->delete();
    }

    /**
     * 根据商品id,删除购物车对应的商品
     * 登陆了删数据库的
     * 未登录删除cookie的
     *
     */
    public function removeGoods($goods_id)
    {
        $userinfo=login();
        if($userinfo){
            $cond=[
                'goods_id'=>$goods_id,
                'member_id'=>$userinfo['id']
            ];
            if($this->where($cond)->delete()&&$this->getList()){
                $return=[
                    'status'=>1,
                    'cook'=>1
                ];
            }else{
                $return=[
                    'status'=>1,
                    'cook'=>0
                ];
            }
            return $return;
        }else{
            $key=C('COOKIE_SHOPPING_CAR');
            //获取cookie的购物车信息
            $cookie_car=cookie($key);

            //删除对应的商品
            unset($cookie_car[$goods_id]);
            cookie($key,$cookie_car,604800);
            if(!cookie($key)){
                $return=[
                    'status'=>1,
                    'cook'=>0
                ];
            }else{
                $return=[
                    'status'=>1,
                    'cook'=>1
                ];
            }
            return $return;
        }
    }


    public function getList()
    {
        return $this->select();
    }
}