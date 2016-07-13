<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/9
 * Time: 20:18
 */

namespace Home\Model;


use Think\Model;

class AddressModel extends Model
{

    protected $patchValidate=true;
    protected $_validate=[
        ['name','require','收货人姓名不能为空'],
        ['province_name','require','收货人省份不能为空'],
        ['city_name','require','收货人城市不能为空'],
        ['area_name','require','收货人区县不能为空'],
        ['detail_address','require','详细地址不能为空'],
        ['tel','require','手机号不能为空'],
    ];

    /**
     * 为指定用户添加收货地址
     * @return mixed
     */
    public function addAddress()
    {
        $userinfo=login();
        //如果勾选了设置默认地址,则先将对应用户的is_default这个字段全都设置为0,再修改
        if(isset($this->data['is_default'])){
            $this->where(['member_id'=>$userinfo['id']])->setField('is_default',0);
        }
        $this->data['member_id']=$userinfo['id'];
        return $this->add();
    }

    /**
     * 获取用户的收货地址信息
     * @return mixed
     */
    public function getAddressList()
    {
        $userinfo=login();
        return $this->where(['member_id'=>$userinfo['id']])->select();
    }

    /**
     * 通过地址id和用户id获取地址信息,
     * 这里看似不需要用户id,但是为了防止数据被篡改,万一用户是想获取别人的,随便输的地址id怎么办,
     * 因此这个用户的id是必须的,而且还不能是提交过来的,所以我们用session里面的数据,
     * @param $id  地址id
     * @return mixed
     */
    public function getListById($id,$field='*')
    {
        $userinfo=login();
        $cond=[
            'id'=>$id,
            'member_id'=>$userinfo['id']
        ];
        return $this->field($field)->where($cond)->find();
    }

    /**
     * 修改地址
     * @return bool
     */
    public function modifyAddress()
    {
        $userinfo=login();
        $id=$this->data['id'];
        unset($this->data['id']);
        $cond=[
            'id'=>$id,
            'member_id'=>$userinfo['id']
        ];
        //如果勾选了设置默认地址,则先将对应用户的is_default这个字段全都设置为0,再修改
        if(isset($this->data['is_default'])){
            $this->where(['member_id'=>$userinfo['id']])->setField('is_default',0);
        }
        return $this->where($cond)->save();
    }

    /**
     * 修改默认地址
     * @param $id
     * @return bool
     */
    public function setDefaultAddress($id)
    {
        $userinfo=login();
        $cond=[
            'id'=>$id,
            'member_id'=>$userinfo['id']
        ];

        $this->where(['member_id'=>$userinfo['id']])->setField('is_default',0);
        return $this->where($cond)->setField('is_default',1);
    }
}