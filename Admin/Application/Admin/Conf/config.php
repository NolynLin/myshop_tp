<?php
define('PZ_PATH','http://admin.shop.com/Public/');
return array(
    /* 定义public文件路径*/
    //'配置项'=>'配置值'
    'TMPL_PARSE_STRING'=>array(
        '__CSS__'=>PZ_PATH.'css',
        '__JS__'=>PZ_PATH.'js',
        '__IMG__'=>PZ_PATH.'images',
    ),
    'DEFAULT_CONTROLLER'    =>  'Supplier', // 默认控制器名称
);