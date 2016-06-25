<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 14:37
 */

namespace Admin\Model;


use Think\Model;

class ArtidArtcatidModel extends Model
{
    public function getArCatid($id)
    {
        return $this->field('art_catid')->where(['artid'=>$id])->find();

    }

}