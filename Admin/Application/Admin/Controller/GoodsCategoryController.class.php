<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 10:48
 */

namespace Admin\Controller;


use Think\Controller;

class GoodsCategoryController extends Controller
{
    /**
     * @var \Admin\Model\GoodsCategoryModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('GoodsCategory');
    }

    /**
     * 分页展示文章分类列表 支持搜索功能
     */
    public function index()
    {
        //将数据传给MODEL方法
        $rows=$this->_model->getList();
        $this->assign('rows',$rows);
        $this->display();
    }

    public function add()
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->add()===false){
                $this->error('添加失败');
            }else{
                $this->success('添加成功',U('index'));
            }
        }else{
            $rows=json_encode($this->_model->getList());
            $this->assign('rows',$rows);
            $this->display();
        }
    }

    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->save()===false){
                $this->error('修改失败');
            }else{
                $this->success('修改成功',U('index'));
            }
        }else{
            //回显
            //获取所有分类
            $row=$this->_model->find($id);
            $rows=json_encode($this->_model->getList());
            $this->assign('rows',$rows);
            dump($row);
            $this->assign($row);
            $this->display('add');
        }
    }

    /**
     * 根据id去逻辑移除文章分类
     */
    public function remove($id)
    {
        $data=[
            'id'=>$id,
            'status'=>-1,
            'name'=>['exp','concat(name,"_del")']
        ];
        if($this->_model->setField($data)===false){
            $this->error(getError($this->_model));
        }else{
            $this->success('移除成功',U('index'));
        }
    }
}