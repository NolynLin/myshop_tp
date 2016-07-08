<?php

/**
 * 整合ueditor插件.
 * kunx-edu <kunx-edu@qq.com>
 */

namespace Org\Util;

/**
 * Description of Ueditor
 *
 * @author qingf
 */
class Ueditor {

    private $_config = array(); //上传工具类配置
    private $_output = ''; //上传结果

    /**
     * 构造方法.
     * @param array $config
     */

    public function __construct(array $config = array()) {
        $this->_config  = $config;
        $config_file    = __DIR__ . '/Ueditor/config.json';
        $config_content = file_get_contents($config_file);
        $CONFIG         = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $config_content), true);
        $action         = I('get.action');
        switch ($action) {
            case 'config':
                $this->_output = json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
                $config        = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize"    => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName     = $CONFIG['imageFieldName']; //图片表单的名字
                $this->_output = json_encode($this->_uploadFile($config, $fieldName));
                break;
            /* 上传文件 */
            case 'uploadfile':
                $config        = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize"    => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                $fieldName     = $CONFIG['fileFieldName'];
                $this->_output = json_encode($this->_uploadFile($config, $fieldName));
                break;
            case 'listimage':
                $this->_output = json_encode($this->_getList());
                break;
        }
    }

    /**
     * 执行最终的上传.
     * @param array $ueditor_config
     * @param type $field_name
     * @return type
     */
    private function _uploadFile(array $ueditor_config, $field_name) {
        $config = $this->_config;
        //扩展名
        $exts   = $ueditor_config['allowFiles'];
        $exts   = array_map($exts, function($ext) {
            return str_replace('.', '', $ext);
        });
        $config['exts']     = $exts;
        //保存路径
        $config['savePath'] = $ueditor_config['imagePathFormat'];

        //大小
        $upload    = new \Think\Upload($config);
        $file_info = $upload->uploadOne($_FILES[$field_name]);

        if (!$file_info) {
            $return = array(
                'state' => $upload->getError(),
            );
        } else {
            if ($upload->driver == 'Qiniu') {
                $url      = $file_info['url'];
                $filename = $file_info['savename'];
            } else {
                //返回从网站根路径开始路径,避免使用pathinfo模式导致的ueditor解析失败.
                $url      = __ROOT__ . $this->_config['rootPath'] . $file_info['savepath'] . $file_info['savename'];
                $url      = str_replace(realpath(__ROOT__), '', $url);
                
//                $host     = 'http://' . $_SERVER['HTTP_HOST'];
                
                //获取url完整地址
                $self = U('','','',true);
                preg_match('/^[^\/]+\/\/[^\/]+(.*)$/',$self,$url_info);
                //只获取协议和域名部分
                $host = str_replace($url_info[1], '', $self);
                $url      = $host . $url;
                $filename = $file_info['savename'];
            }
            $return = array(
                "state"    => 'SUCCESS',
                "url"      => $url,
                "title"    => $filename,
                "original" => $file_info['name'],
                "type"     => $file_info['ext'],
                "size"     => $file_info['size'],
            );
        }
        return $return;
    }

    /**
     * 获取执行结果.
     * @return type
     */
    public function output() {
        return $this->_output;
    }

    /**
     * 图片列表
     * @return type
     */
    private function _getList() {
        $upload = new \Think\Upload($this->_config);
        $return = array();
        if ($upload->driver == 'Qiniu') {
            $storge = new \Think\Upload\Driver\Qiniu\QiniuStorage($upload->driverConfig);

            $list = $storge->getList();
            foreach ($list['items'] as $item) {
                $return['list'][] = array(
                    'url'   => 'http://' . $upload->driverConfig['domain'] . '/' . $item['key'],
                    'mtime' => $item['putTime'] / 1000,
                );
            }
            $return['start'] = 0;
            $return['state'] = 'SUCCESS';
            $return['total'] = count($return['list']);
        }
        return $return;
    }

}
