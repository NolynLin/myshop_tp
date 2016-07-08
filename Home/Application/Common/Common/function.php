<?php
/**
 * 加盐加密
 * @param $password
 * @param $salt
 * @return string
 */
function salt_mcrypt($password,$salt){
    return md5(md5($password).$salt);
}

/**
 * @param $email     用户输入的邮箱
 * @param $subject   邮件的标题
 * @param $content   邮件的内容
 * @param $username  注册输入的用户名
 * @return array
 * @throws phpmailerException
 */
function sendEmail($email,$subject,$content,$username)
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
    $mail->addAddress($email, $username);     // Add a recipient
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject =$subject;
    $mail->Body    = $content;
    $mail->CharSet = 'UTF-8';
    //判断邮箱发送的状态,
    if(!$mail->send()) {
        return [
            'status'=>0,
            'msg'=>$mail->ErrorInfo,
        ];
    } else {
        return [
            'status'=>1,
            'msg'=>'',
        ];
    }
}

/**
 * 列表展示错误信息
 * @param \Think\Model $model
 * @return string
 */
function getError(\Think\Model $model){
    $errors=$model->getError();
    if(!is_array($errors)){
        $errors=array($errors);
    }
    $html='<ul>';
    foreach($errors as $error){
        $html.='<li>'.$error.'</li>';
    }
    $html.='</ul>';
    return $html;
}
/**
 * 存在则设值,没有就获取值 获取登陆用户信息
 * @param null $data
 * @return mixed
 */
function login($data=null)
{

    if (is_null($data)) {
//        dump(session('USER_INFO'));
        return session('USER_INFO');
    } else {
        session('USER_INFO', $data);
    }
}
/**
 * 设置保存自动登陆的cookie信息
 * @param null $data
 * @return mixed
 */
function auto_login($data=null)
{
    if (is_null($data)) {
        return cookie('MEMBER_AUTO_LOGIN_TOKEN');
    } else {
        cookie('MEMBER_AUTO_LOGIN_TOKEN',$data,604800);
    }
}

/**
 * 使用redis的设置
 */
function get_redis()
{
    //获取redis实例
    $redis=new Redis();
    //连接redis
    $redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
    return $redis;
}

