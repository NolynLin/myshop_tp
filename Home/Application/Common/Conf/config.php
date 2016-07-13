<?php
define('PZ_PATH','http://www.shop.com');
return array(
    'URL_MODEL'            =>2, /*设置// URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式*/
    //分页相关的配置
    'PAGE_SETTING'=>[
        'PAGE_SIZE'=>2,
        //分页样式,加一个%HEADER%，显示总数据条数
        'PAGE_THEME'=>'%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
    ],
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
    'DB_NAME'               =>  'tpmyshop',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '654321',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  '',    // 数据库表前缀
    'DB_PARAMS'          	=>  array(), // 数据库连接参数
    'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
    /* 定义public文件路径*/
    //'配置项'=>'配置值'
    'TMPL_PARSE_STRING'=>array(
        '__CSS__'=>PZ_PATH.'/Public/css',
        '__JS__'=>PZ_PATH.'/Public/js',
        '__IMG__'=>PZ_PATH.'/Public/images',
        '__UPLOADIFY__'=>PZ_PATH.'/Public/ext/uploadify',
        '__LAYER__'=>PZ_PATH.'/Public/ext/layer',
        '__ZTREE__'=>PZ_PATH.'/Public/ext/ztree',
        '__TREEGRID__'=>PZ_PATH.'/Public/ext/treegrid',
        '__UEDITOR__'=>PZ_PATH.'/Public/ext/ueditor',
        '__JQUERY_VALIDATE__'=>PZ_PATH.'/Public/ext/jquery-validate',
    ),
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
//    'UPLOAD_SETTING' =>require 'upload.php',
     //调试页面
    'SHOW_PAGE_TRACE' =>true,

'ACCESS_IGNORE'=>[
    //配置所有用户都能访问的页面
    'IGNORE'=>[
        'Home/Member/login',
        'Home/Captcha/captcha',
        'Home/Index/index',
    ] ,
    //登陆后用户能访问的公共页面
    'USER_IGNORE'=>[
        'Home/Member/logout',
    ]
],
    'COOKIE_PREFIX'=>'member_shop_com_',
//    'COOKIE_PREFIX'=>'admin_shop_com_',
//Redis Session配置
    'SESSION_AUTO_START'	=>  true,	// 是否自动开启Session
    'SESSION_TYPE'			=>  'Redis',	//session类型
    'SESSION_PERSISTENT'    =>  1,		//是否长连接(对于php来说0和1都一样)
    'SESSION_CACHE_TIME'	=>  1,		//连接超时时间(秒)
    'SESSION_EXPIRE'		=>  0,		//session有效期(单位:秒) 0表示永久缓存
    'SESSION_PREFIX'		=>  'sess_',		//session前缀
    'SESSION_REDIS_HOST'	=>  '127.0.0.1', //分布式Redis,默认第一个为主服务器
    'SESSION_REDIS_PORT'	=>  '6379',	       //端口,如果相同只填一个,用英文逗号分隔
    'SESSION_REDIS_AUTH'    =>  '',    //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔
    //数据缓存存入redis
    'DATA_CACHE_TYPE'      =>  'Redis',//数据缓存机制
    'REDIS_HOST'           =>  '127.0.0.1',
    'REDIS_PORT'           =>  '6379',
    'COOKIE_SHOPPING_CAR'  =>  'MEMBER_SHOPPING_CAR'
);