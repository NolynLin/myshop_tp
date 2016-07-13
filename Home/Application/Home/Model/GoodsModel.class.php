<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/7
 * Time: 17:52
 */

namespace Home\Model;


use Think\Model;

class GoodsModel extends Model
{
    /**
     * 获取对应状态的商品
     * @param $pram
     * @return mixed
     */
    public function getGoodsByPram($pram)
    {
        $cond=[
            'is_on_sale'=>1,
            'status'=>1,
            'goods_status &'.$pram
        ];
        return $this->where($cond)->select();
    }

    /**
     * 根据id获取商品的详细信息
     * @param $id
     * @return mixed
     */
    public function getGoodsInfo($id)
    {
        $row=$this->field('g.*,b.name as bname,ga.content')->alias('g')->where(['is_on_sale'=>1,'g.status'=>1,'g.id'=>$id])->join('__BRAND__ AS b on g.brand_id=b.id')->join('__GOODS_INTRO__ as ga on g.id=ga.goods_id')->find();
        $row['galleries']=M('GoodsGallery')->where(['goods_id'=>$id])->getField('path',true);
        return $row;
    }
}