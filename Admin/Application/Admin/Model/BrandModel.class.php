<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 9:23
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class BrandModel extends Model
{
    // 是否批处理验证
    protected $patchValidate    =   true;
    protected $_validate = [
        //验证品牌名
        ['name','require','品牌名称不能为空'],
        ['name','','品牌名称已存在',self::EXISTS_VALIDATE ,'unique','register'],
        //验证排序
        ['sort','require','供货商排序不能为空'],
        ['sort','number','供货商排序只能是数字'],
    ];

    /**
     * 获取分页代码 搜索结果
     * @param array $cond
     */
    public function getRandData(array $cond=[]){
        //状态大于0的数据
        $cond=array_merge(['status'=>['egt',0]],$cond);
        //获取分页配置
        $page_setting=C('PAGE_SETTING');
        //获取总条数
        $count=$this->where($cond)->count();
       //取得分页实例
        $page=new Page($count,$page_setting['PAGE_SIZE']);
        //theme设置分页样式的关键字
        $page->setConfig('theme',$page_setting['PAGE_THEME']);
        //取得分页html代码
        $page_html=$page->show();
        //获取数据
        $rows=$this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        //将数据拼接
        return compact('rows','page_html');
    }
    //获取满足条件的品牌列表
    public function getList()
    {
        return $this->where(['status'=>['gt',0]])->select();
    }

}