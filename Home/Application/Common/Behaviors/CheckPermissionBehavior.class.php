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
        //获取公共不用行为的url
        $ignore_setting = C('ACCESS_IGNORE');
        $ignore=$ignore_setting['IGNORE'];
        $user_ignore=$ignore_setting['USER_IGNORE'];
        $ignore=array_merge($ignore,$user_ignore);
        //如果是公共的就直接进入到这个url,如果不是,那么就继续往下判断
        if(in_array($url,$ignore)){
            return true;
        }
        echo 111;
        //获取用户信息
        $memberinfo = login();
        //首先验证自动登陆
        if(!$memberinfo){
            $rst=D('Member')->autologin();
            if(!$rst){
                redirect(U('Member/login','','请先登录'));
            }
            return true;
        }else{
            return true;
        }

    }

}