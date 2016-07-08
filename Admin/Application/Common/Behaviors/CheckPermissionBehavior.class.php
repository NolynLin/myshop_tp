<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 15:06
 */

namespace Common\Behaviors;
use Think\Behavior;
class checkPermissionBehavior extends Behavior
{
    public function run(&$params)
    {
        //获取当前登陆的url地址
        $url=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        //获取所有用户都能访问的页面
        $ignore_setting = C('ACCESS_IGNORE');
        $ignore=$ignore_setting['IGNORE'];
        //如果是公共的就直接进入到这个url,如果不是,那么就继续往下判断
        if(in_array($url,$ignore)){
            return true;
        }
        //用app_begin后,为什么session里面的数据和我设置的不一样?并且去行为那里的时候,获取不到session????
//        dump($userinfo);exit;
        //获取用户信息
        $userinfo = login();
        //首先验证自动登陆
        if(!$userinfo){
//            echo 2;exit;
            //
            $userinfo=D('Admin')->autologin();
        }
//        dump($userinfo);exit;
        //如果登陆了,并且是超级管理员,那么就不继续往下判定访问页面了,
        if($userinfo && $userinfo['username']=='admin'){
            return true;
        }
        //获取允许访问权限列表
        $alow_path=permission_pathes();
        //如果用户登陆了,去拼接可访问的url地址
        $urls=[];
        if($userinfo){
            //获取登陆后能访问的公共页面
            $urls=$ignore_setting['USER_IGNORE'];
            //拼接全部能访问的页面
            $urls=array_merge($urls,$alow_path);
        }
        //判定当前url地址是否在允许登陆的范围内
        if(!in_array($url,$urls)){
            echo "<script>alert('您的权限不够,请重新登陆');top.location.href='http://admin.shop.com'</script>";
            exit;
            redirect(U("Admin/Admin/login"),3,'无权访问');
        }
    }

}