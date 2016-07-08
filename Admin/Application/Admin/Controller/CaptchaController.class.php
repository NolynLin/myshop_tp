<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 11:54
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Verify;

class CaptchaController extends Controller
{
    /**
     * 获取验证码
     */
    public function getcaptcha()
    {
        $verify_setting=[
            'length'    =>  4,
        ];
        $verify=new Verify($verify_setting);
        $verify->entry();
    }
}