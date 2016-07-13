<?php
//将错误信息批量返回并显示
function getError(\Think\Model $model)
{
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
 * @param array $data          一个二维数组
 * @param  string $name_filed  要获取的option里面value的字段名
 * @param string $value_filed  要获取的option里面值得字段名
 * @param string $name         对应数据表要保存的字段名
 * @return string html代码
 */
function getSelectHtml (array $data,$name_filed,$value_filed,$name='',$default_value='')
{
    $html='<select name="'.$name.'" class="'.$name.'">';
    $html.='<option value="">请选择</option>';
    foreach($data as $key=>$val){
        if((string)$val[$name_filed]===$default_value){
            $html.='<option value="'.$val[$name_filed].'" selected="selected">'.$val[$value_filed].'</option>';
        }else{
    $html.='<option value="'.$val[$name_filed].'" >'.$val[$value_filed].'</option>';
        }
    }
    $html.='</select>';
    return $html;
}

/**
 * 生成加盐加密密码
 * @param $password
 * @param $salt
 * @return string
 */
function salt_mcrypt($password,$salt){
    return md5(md5($password).$salt);
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
        return cookie('USER_AUTO_LOGIN_TOKEN');
    } else {
        cookie('USER_AUTO_LOGIN_TOKEN',$data,604800);
    }
}
/**
 * 存在则设值,没有就获取值 获取权限路径
 * @param null $data
 * @return mixed
 */
function permission_pathes($data=null){
    if(is_null($data)){
        $pathes=session('PERMISSIONS_PATHS');
        if(!is_array($pathes)){
            $pathes=[];
        }
        return $pathes;
    }else{
        session('PERMISSIONS_PATHS',$data);
    }
}

/**
 * 将权限id保存到session中
 * @param null $data
 * @return array|mixed
 */
function permission_pids($data=null){
    if(is_null($data)){
        $pids=session('PERMISSION_PIDS');
        if(!is_array($pids)){
            $pids=[];
        }
        return $pids;
    }else{
        session('PERMISSION_PIDS',$data);
    }
}
