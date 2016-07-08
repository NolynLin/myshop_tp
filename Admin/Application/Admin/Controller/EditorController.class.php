<?php

/**
 * 富文本编辑器服务.
 * kunx-edu <kunx-edu@qq.com>
 */

namespace Admin\Controller;

/**
 * Description of EditorController
 *
 * @author qingf
 */
class EditorController extends \Think\Controller{
    
    /**
     * ueditor后台服务部分.
     * 比如获取配置
     * 比如上传文件和图片,以及读取文件列表.
     */
    public function ueditor(){
        $data = new \Org\Util\Ueditor(C('UPLOAD_SETTING'));
        echo $data->output();
        exit;
    }
}
