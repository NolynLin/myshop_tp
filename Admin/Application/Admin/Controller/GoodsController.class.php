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
        //获取搜索的关键字model层需要做具体查询，这里用`article`.这样的方式
        $name=I('get.name');
        $cond=[];
        if(!empty($name)){
            $cond['name']=['like','%'.$name.'%'];
        }
        $rows=$this->_model->getGoodsPage();
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
            if($this->_model->create('','register')===false){
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
            $this->assign('row',$row);
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