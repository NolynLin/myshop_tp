<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/9
 * Time: 16:21
 */

namespace Home\Model;


use Think\Model;

class LocationsModel extends Model
{
    /**
     * 根据父级id查找指定的子级城市
     * @param int $parent_id
     * @return mixed
     */
    public function getListByParentId($parent_id=0)
    {
        return $this->where(['parent_id'=>$parent_id])->select();
    }


}