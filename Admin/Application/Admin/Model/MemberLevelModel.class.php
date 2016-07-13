<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/11
 * Time: 22:08
 */

namespace Admin\Model;


use Think\Model;

class MemberLevelModel extends Model
{
    /**
     * 获取会员等级列表
     * @return mixed
     */
    public function getList()
    {
        return $this->where('status=1')->order('sort')->select();
    }

}