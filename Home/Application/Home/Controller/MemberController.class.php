<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 10:07
 */

namespace Home\Controller;


use Think\Controller;

class MemberController extends Controller
{
    /**
     * @var \Home\Model\MemberModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Member');
        $meta_titles=[
            'reg'=>'用户注册',
            'login'=>'用户登录',
            'useraddress'=>'地址管理',
        ];
        $meta_title=(isset($meta_titles[ACTION_NAME])?$meta_titles[ACTION_NAME]:'用户登录');
        $this->assign('meta_title',$meta_title);
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
     * 用户注册
     */
    public function reg()
    {
        if(IS_POST){
            if($this->_model->create('','reg')===false){

                $this->error(getError($this->_model));
            }
            if($this->_model->addMember()===false){
                $this->error(getError($this->_model));
            }
            $this->success('注册成功',U('index'));
        }else{
            $this->display('reg');
        }
    }

    /**
     * 激活账号
     * @param $email
     * @param $register_token
     */
    public function checkEmail($email,$register_token)
    {
        $cond=[
            'email'=>$email,
            'register_token'=>$register_token,
            'status'=>0,
        ];
        //如果能查出数据,则表示是当前用户操作
        if($this->_model->where($cond)->count()){
            //改变用户状态,
            $this->_model->where($cond)->setField('status',1);
            $this->success('激活成功',U('Member/index'));
        }else{
            $this->error('验证失败',U('Member/index'));
        }
    }
    //通过ajax验证用户名和邮箱是否重复
    public function checkByPram()
    {
        $cond=I('get.');
        if($this->_model->where($cond)->count()){
            $this->ajaxReturn(false);
        }else{
            $this->ajaxReturn(true);
        }
    }

    /**
     * 用户登陆
     */
    public function login()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->login()===false){
                $this->error(getError($this->_model));
            }
            //查看用户是否是从结算页面登录的
            $url=cookie('__FORWARD__');
            if(!$url){
                $url=U('Index/index');
            }
            $this->success('登陆成功',$url);
        }else{
            $this->display();
        }
    }

    /**
     * 注销登陆
     */
    public function logout()
    {
        session(null);
        cookie(null);

        $this->success('退出成功',U('Index/index'));
    }

    /**
     * 用户登录信息展示
     */
    public function usertips()
    {
        $userinfo=login();
        if($userinfo['username']){
            $this->ajaxReturn($userinfo['username']);
        }else{
            $this->ajaxReturn(false);
        }
    }

    /**
     * 用户添加收货地址
     */
    public function useraddress()
    {
        //获取地区数据并展示
        $locations_model=D('Locations');
        //首次进入页面,默认只是展示省级城市,所以parent_id不写,默认为0,其他的数据通过ajax获取,再传入对应的pid
        $provinces=$locations_model->getListByParentId();
        $this->assign('provinces',$provinces);
        //展示当前用户的所有收货地址
        $addresses_model=D('Address');
        $addresses=$addresses_model->getAddressList();
        $this->assign('addresses',$addresses);
        $this->display();
    }

    /**
     * 通过ajax获取子级城市
     * @param $parent_id
     */
    public function getListByParentId($parent_id)
    {
        $locations_model=D('Locations');
       $this->ajaxReturn($locations_model->getListByParentId($parent_id));
    }

    /**
     * 添加收货地址
     */
    public function addLocation()
    {
        $address_model=D('Address');
        if($address_model->create()===false){
            $this->error(getError($address_model));
        }
        if($address_model->addAddress()===false){
            $this->error(getError($address_model));
        }
        $this->success('添加成功',U('useraddress'));
    }

    /**
     * 修改地址
     * @param $id
     */
    public function modifyAddress($id)
    {
        $address_model = D('Address');
        if(IS_POST){
            if($address_model->create()===false){
                $this->error(getError($address_model));
            }
            if($address_model->modifyAddress()===false){
                $this->error(getError($address_model));
            }
            $this->success('修改成功',U('useraddress'));
        }else {
            //获取地区数据并展示
            $locations_model = D('Locations');
            //首次进入页面,默认只是展示省级城市,所以parent_id不写,默认为0,其他的数据通过ajax获取,再传入对应的pid
            $provinces = $locations_model->getListByParentId();
            $this->assign('provinces', $provinces);

            //获取收货地址详情
            $row = $address_model->getListById($id);
            $this->assign('row', $row);
            $this->display();
        }
    }

    /**
     * 修改默认地址
     * @param $id
     */
    public function setDefaultAddress($id)
    {
        $address_model = D('Address');
        if($address_model->setDefaultAddress($id)===false){
            $this->error(getError($address_model));
        }
        $this->success('修改默认地址成功');
    }

    /**
     * 删除收货地址
     * @param $id
     */
    public function removeAddress($id)
    {
        $userinfo=login();
        $address_model = D('Address');
        $cond=[
            'id'=>$id,
            'member_id'=>$userinfo['id']
        ];
        if($address_model->where($cond)->delete()===false){
            $this->error(getError($address_model));
        }
        $this->success('删除成功');
    }
}