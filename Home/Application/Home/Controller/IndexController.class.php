<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/7
 * Time: 12:49
 */

namespace Home\Controller;


use Think\Controller;

class IndexController extends Controller
{
    protected function _initialize(){
        //获取用户登陆信息
        $this->assign('userinfo',login());
        //判断是否需要一进页面就展示商品分类下拉列表,根据方法去判断
        if(ACTION_NAME=='index'){
            $show_category=true;
        }else{
            $show_category=false;
        }
        $this->assign('show_category',$show_category);
        //分类数据和帮助文章列表数据不会频繁发生变化,但是请求又比较平凡,所以就进行缓存
        if(!$goods_categories=S('goods_categories')){
            //如果没有缓存的时候,就去获取
            //获取商品分类列表
            $goods_category_model=D('GoodsCategory');
            $goods_categories=$goods_category_model->getList('id,name,parent_id');
            //获取出来后并进行缓存
            S('goods_categories',$goods_categories,3600);
        }
        $this->assign('goods_categories',$goods_categories);
        //缓存帮助文章数据
        if(!$help_articles=S('help_articles')){
            //获取帮助文章数据
            $article_model=D('Article');
            $help_articles=$article_model->getHelpList();
            //将文章数据进行缓存
            S('help_articles',$help_articles,3600);
        }
        $this->assign('help_articles',$help_articles);
    }

    /**
     * 展示精品,热销,新品
     */
    public function index()
    {
        $goods_model=D('Goods');
        //获取精品,新品,热销商品
        $data=[
          'best_goods'=>$goods_model->getGoodsByPram(1),
          'new_goods'=>$goods_model->getGoodsByPram(2),
          'hot_goods'=>$goods_model->getGoodsByPram(4),
        ];
        $this->assign($data);
        $this->display();
    }

    /**
     * 获取商品详情
     * @param $id
     */
    public function goods($id)
    {
        $goods_model=D('Goods');
        $goods_info=$goods_model->getGoodsInfo($id);
        $this->assign('goods_info',$goods_info);
        $this->display();
    }
}