<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/14
 * Time: 12:34
 */

namespace Home\Controller;


use Think\Controller;

class TestController  extends  Controller
{

    /**
     * 邮件发送
     */
    public function sendEmail()
    {
        $obj = new TestThread('838953989@qq.com','欢迎注册','一次注册,终身受益','Nolyn');
        $rst=$obj->start();
        echo $rst;
    }

    /**
     * php使用coreseek
     * @param string $keyword
     */
    public function cssearch($keyword = '裤') {
        //引入sphinx的类库
        vendor('Sphinx.sphinxapi');
        //取得实例
        $spinx     = new \SphinxClient();
        //建立连接
        $spinx->SetServer('127.0.0.1', 9312);
        //搜索条数
        $spinx->SetLimits(0, 50);
        //设置只有一个关键词匹配
        $spinx->SetMatchMode(SPH_MATCH_ANY);
        //执行query
        $rst       = $spinx->Query($keyword, '*');
        //取得商品id
        $goods_ids = array_keys($rst['matches']);
        $model     = M('Goods');
        //取得对应数据
        $list      = $model->where(['id' => ['in', $goods_ids]])->select();
        //配置关键字高亮
        $options   = array(
            'before_match'    => '<span style="color:red;background:lightblue">',
            'after_match'     => '</span>',
            'chunk_separator' => '...',
            'limit'           => 80, //如果内容超过80个字符，就使用...隐藏多余的的内容
        );
//关键字高亮
        $keywords  = array_keys($rst['words']);
        foreach ($list as $index => $item) {
            $list[$index] = $spinx->BuildExcerpts($item, 'mysql', implode(',', $keywords), $options); //使用的索引不能写*，关键字可以使用空格、逗号等符号做分隔，放心，sphinx很智能，会给你拆分的
        }
        //不能用dump,里面有pre,shan不能展示了
        var_dump($list);
    }
}
//
class TestThread extends \Thread{

    private $email, $subject, $content,$username;

    public function __construct($email, $subject, $content,$username) {
        $this->email   = $email;
        $this->subject = $subject;
        $this->content = $content;
        $this->username = $username;
    }
    public function run()
    {
        sendEmail($this->email, $this->subject, $this->content,$this->username);
    }
}
