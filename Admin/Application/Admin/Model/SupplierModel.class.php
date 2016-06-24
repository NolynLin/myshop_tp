<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/24
 * Time: 17:22
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class SupplierModel extends Model
{
    // 是否批处理验证
    protected $patchValidate    =   true;
    protected $_validate = [
        //验证供货商名
        ['name','require','供货商名称不能为空'],
        ['name','','供货商名称已存在',self::EXISTS_VALIDATE ,'unique','register'],
        //验证排序
        ['sort','require','供货商排序不能为空'],
        ['sort','number','供货商排序只能是数字'],
    ];

    /**
     * 获取分页代码，
     * @param array $cond
     */
    public function getSuppPage(array $cond=[]){
        //获取分页配置
        $page_setting=C('PAGE_SETTING');
        //获取总行数
        $count=$this->where($cond)->count();
        //取得分页实例对象
        //分页这里不是很懂？？？？？
        $page=new Page($count,4);
        //theme设置分页的关键字
        $page->setConfig('theme',$page_setting['PAGE_THEME']);
        //取得分页html代码
        $page_html=$page->show();
        //根据条件查询数据
        $rows=$this->where($cond)->page(I('get.p',1),4)->select();
        //返回查询的数据和html代码
//        dump($rows);
//        dump($page_html);exit;
        return compact(['rows','page_html']);
    }
}