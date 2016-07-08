<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/7
 * Time: 17:00
 */

namespace Home\Model;


use Think\Model;

class ArticleModel extends Model
{
    public function getHelpList()
    {
        //获取文章分类列表
        $article_category_model=M('ArticleCategory');
        $article_categories=$article_category_model->where(['is_help'=>1,'status'=>1])->getField('id,name');
        //这里获取的格式,如果是一个文章分类作为键,文章标题作为值,那么就很好在前台展示了
        $data=[];
        foreach($article_categories as $key=>$val){
            $article_titles=$this->field('id,name')->where(['status'=>1,'article_category_id'=>$key])->limit(6)->order('sort')->select();
            $data[$val]=$article_titles;
        }
        return $data;
    }
}