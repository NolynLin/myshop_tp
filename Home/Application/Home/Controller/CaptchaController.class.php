<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 11:22
 */

namespace Home\Controller;


use Think\Verify;

class CaptchaController extends Verify
{
    /**
     * 展示验证码
     */
    public function captcha()
    {
        $setting=['length'=>4];
        $verify=new Verify($setting);
        $verify->entry();
    }
}