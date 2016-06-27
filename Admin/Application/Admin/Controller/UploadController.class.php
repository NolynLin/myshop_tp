<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/26
 * Time: 14:25
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Upload;

class UploadController extends Controller
{
    public function upload()
    {
       //创建upload对象
        $upload = new Upload(C('UPLOAD_SETTING'));
        //执行上传,为什么不能只用upload？？
        $file_info=$upload->uploadOne($_FILES['file_data']);
        //上传成功返回文件的完整路径，失败返回错误信息
        if($file_info){
            if($upload->driver=='Qiniu'){
                $file_url=$file_info['url'];
            }else{
                $file_url=PZ_PATH.'/'. $file_info['savepath'].$file_info['savename'];
            }
            $return = [
                'file_url'=>$file_url,
                'msg'=>'上传成功',
                'status'=>1,
            ];
        }else{
            $return = [
                'file_url'=>'',
                'msg'=>$upload->getError(),
                'status'=>0,
            ];
        }
        //返回上传的结果
        $this->ajaxReturn($return);
    }


}