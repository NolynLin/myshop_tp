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
