<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/5
 * Time: 13:06
 */

namespace Home\Controller;


use Think\Controller;

class TelCaptchaController extends Controller
{

    public function regSms($tel)
    {
        //发送短信
        //引入阿里大鱼
        Vendor('Alidayu.TopSdk');
        $c = new \TopClient;
        $c->appkey = '23399452';     //ak
        $c->secretKey = 'ef510dd57eacb08995937b0215d6accc';     //sk
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("枫Nolyn");
        $code=\Org\Util\String::randNumber(100000, 999999);        //随机的数字验证码
        $data=[
          'product'=>'Nolyn彼倫杂货铺',     //键名是根据配置的短信模板而定的
            'code'=>$code,
        ];
        //发送成功将code保存到session中,用于验证输入的是否正确
        session('reg_tel_code',$code);
        $req->setSmsParam(json_encode($data));   //将数据转成json格式,
        $req->setRecNum($tel);              //要发给谁,这是获取了用户提交的电话号码,在当点击了发送验证码,通过ajax提交
        $req->setSmsTemplateCode("SMS_11490126");      //配置的阿里大鱼的短信模板
        $resp = $c->execute($req);       //执行并接收结果
        dump($resp);
    }

    public function sendEmail()
    {
        Vendor('PHPMailer.PHPMailerAutoload');
        $mail = new \PHPMailer;
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.qq.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = '838953989@qq.com';                 // SMTP username
        $mail->Password = 'stiwpgzmtzgdbcic';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->setFrom('838953989@qq.com', 'Nolyn');
        $mail->addAddress('qf838953989@163.com', 'brother four');     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = '欢迎注册彼倫杂货铺';
        $url = U('Member/Active',['email'=>'kunx-eud@qq.com'],true,true);
        $mail->Body    = '欢迎您注册我们的网站,请点击<a href="'.$url.'">链接</a>激活账号.如果无法点击,请复制以下链接粘贴到浏览器窗口打开!<br />' . $url;
        $mail->CharSet = 'UTF-8';
        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }
}