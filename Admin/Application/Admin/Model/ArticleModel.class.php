<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 12:32
 */

namespace Admin\Model;


use Think\Model;

class ArticleModel extends Model
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
}