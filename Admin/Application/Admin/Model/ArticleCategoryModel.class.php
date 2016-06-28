<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 10:51
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class ArticleCategoryModel extends Model
{
    // 是否批处理验证
    protected $patchValidate    =   true;
    protected $_validate = [
        //验证分类名称
        ['name','require','分类名称不能为空'],
        ['name','','分类名称已存在',self::EXISTS_VALIDATE ,'unique','register'],
        //验证排序
        ['sort','require','分类排序不能为空'],
        ['sort','number','分类排序只能是数字'],
    ];
      //获取分页数据 搜索数据 并返回
    public function getArtCatPage(array $cond=[])
    {
        /**
         * 获取分页代码
         * 1.获取分页配置信息
         * 2.获取总条数
         * 3.取得Page实例
         * 4.调用show方法
         * 获取状态为1的数据
         *将数据拼接并返回
         */
        $page_setting=C('PAGE_SETTING');
        $count=$this->where($cond)->count();
        $page=new Page($count,$page_setting['PAGE_SIZE']);
        //自定义分页样式，显示出总数据条数
        $page->setConfig('theme',$page_setting['PAGE_THEME']);
        $page_html=$page->show();
        $rows=$this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('rows','page_html');
    }
    public function getList()
    {
        return $this->where(['status'=>['egt',0]])->getField('id,name');
    }
}