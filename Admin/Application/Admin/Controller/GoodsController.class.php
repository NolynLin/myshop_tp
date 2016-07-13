<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/28
 * Time: 22:07
 */

namespace Admin\Controller;


use Think\Controller;

class GoodsController extends Controller
{
    /**
     * @var \Admin\Model\GoodsModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Goods');
    }
    /**
     * 获取分页数据 并支持搜索
     */
    public function index()
    {
        //获取搜索的关键字
        $name=I('get.keyword');
        $cond=[];
        if(!empty($name)){
            $cond['name']=['like','%'.$name.'%'];
        }
        //获取商品分类
        $goodsCategorie=I('get.goodsCategories');
        if(!empty($goodsCategorie)){
            $cond['goods_category_id']=$goodsCategorie;
        }
        //获取品牌
        $brand=I('get.brands');
        if(!empty($brand)){
            $cond['brand_id']=$brand;
        }
        //获取推荐
        $intro_type=I('get.intro_type');
        if(!empty($intro_type)){
            $cond[]=['goods_status &'.$intro_type];
        }

        //获取是否上架
        $is_on_sale=I('get.is_on_sale');
        if(strlen($is_on_sale)){
            $cond['is_on_sale']=$is_on_sale;
        }
        //1.回显商品分类,
        $goodsCategoryModel=D('GoodsCategory');
        $goodsCategories=$goodsCategoryModel->getList();
        $this->assign('goodsCategories',$goodsCategories);
        //2.显示商品列表
        $brandModel=D('Brand');
        $brands=$brandModel->getList();
        $this->assign('brands',$brands);
        //显示促销信息
        $intro_type=[
         ['id'=>1,'name'=>'精品'],
         ['id'=>2,'name'=>'新品'],
         ['id'=>4,'name'=>'热销'],
        ];
        $this->assign('intro_type',$intro_type);
        //显示是否上架
        $is_on_sale=[
            ['id'=>0,'name'=>'下架'],
            ['id'=>1,'name'=>'上架'],
        ];
        $this->assign('is_on_sale',$is_on_sale);

        $rows=$this->_model->getGoodsPage($cond);
        $this->assign($rows);
        $this->display();
    }
    /**
     * 添加
     */
    public function add()
    {
        if(IS_POST){
            //为什么我这边要加上标记才能获取到??直接用self::MODEL_INSERT这个模式不行
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }

//            dump($this->_model->create('','register'));
            if($this->_model->addGoods()===false){
                $this->error(getError($this->_model));
            }else{
                $this->success('添加成功',U('index'));
            }
        }else{
           $this->_before_view();
            $this->display();
        }
    }

    /**
     * @param $id
     * 根据id修改当前商品信息
     */

    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->saveGoods($id)===false){
                $this->error(getError($this->_model));
            }else{
                $this->success('修改成功',U('index'));
            }
        }else{
            //1.回显商品信息,这里要处理商品的status,所以单独写个方法
            $row=$this->_model->getGoodsInfo($id);
            $this->assign($row);
            $this->_before_view();
            //回显商品图片
            $this->display('add');
        }
    }
    /**
     * 物理删除文章
     * @param $id
     */
    public function remove($id)
    {
        if($this->_model->removeGoods($id)===false){
            $this->error(getError($this->_model));
        }else{
            $this->success('移除成功',U('index'));
        }
    }

    /**
     * 获取公共数据
     */
    public function _before_view()
    {
        //1.回显商品分类,用ztree展示
        $goodsCategoryModel=D('GoodsCategory');
        $goodsCategories=$goodsCategoryModel->getList();
        $this->assign('goodsCategories',json_encode($goodsCategories));
        //2.显示商品列表
        $brandModel=D('Brand');
        $brands=$brandModel->getList();
        $this->assign('brands',$brands);
        //3.显示供货商列表
        $supplierModel=D('Supplier');
        $suppliers=$supplierModel->getList();
        $this->assign('suppliers',$suppliers);
        //4.显示会员等级
        $member_level_model=D('MemberLevel');
        $member_levels=$member_level_model->getList();
        $this->assign('member_levels',$member_levels);
    }

    /**
     * 通过ajax移出图片
     */
    public function removePic($pic_id)
    {
        $goodsGalleryModel=M('GoodsGallery');
        if($goodsGalleryModel->delete($pic_id)===false){
            $return = [
                'status'=>0,
            ];
        }else{
            $return = [
                'status'=>1,
                ];
        }
       $this->ajaxReturn($return);
    }
}