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
            if($this->_model->addCategory()===false){
                $this->error(getError($this->_model));
            }else{
                $this->success('添加成功',U('index'));
            }
        }else{
            $this->_before_view();
            $this->display();
        }
    }

    /**
     * 编辑商品分类,用nestedsets工具自动计算左右节点和层级
     * @param $id
     */
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->saveCategory()===false){
                $this->error(getError($this->_model));
            }else{
                $this->success('修改成功',U('index'));
            }
        }else{
            //回显

            $row=$this->_model->find($id);
            $this->assign($row);
            //获取所有分类,并添加一个顶级分类
            $this->_before_view();
            $this->display('add');
        }
    }

    /**
     * 根据id去物理删除商品分类,并自动使用nestedsets工具计算左右节点和层级
     */
    public function remove($id)
    {
        if($this->_model->removeCategory($id)===false){
            $this->error(getError($this->_model));
        }else{
            $this->success('移除成功',U('index'));
        }
    }
    //将分类数据里面添加一个顶级分类的数组,
    public function _before_view()
    {
        $rows=$this->_model->getList();
        //将顶级分类添加进数组
        array_unshift($rows,['id'=>0,'name'=>'顶级分类','parent_id'=>0]);
        //将数组转成json对象传过去
        $rows=json_encode($rows);
        $this->assign('rows',$rows);
    }
}