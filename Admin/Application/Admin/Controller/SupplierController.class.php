<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/24
 * Time: 15:41
 */

namespace Admin\Controller;
use Think\Controller;

class SupplierController extends Controller
{
    /**
     * @var \Admin\Model\SupplierModel
     */
    private $_model=null;
    protected function _initialize()
    {
        $this->_model=D('Supplier');
    }
    //列表显示，显示供货商列表，并且分页显示，排除状态为-1的数据
    public function index()
    {
        //搜索
        $name=I('get.name');
        //查询状态大于等于0的数据
        $cond['status']=['egt',0];
//        如果是模糊查询的话,添加一条查询规则
        if(!empty($name)){
            $cond['name']=['like','%'.$name.'%'];
        }
        //写数据查询
        $data= $this->_model->getSuppPage($cond);
        $this->assign($data);
        $this->display();
    }
//https://note.wiz.cn/pages/manage/biz/applyInvited.html?code=ucv7m6
    /**
     * 编辑供货商
     */
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->save() === false){
                $this->error(getError($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            $row=$this->_model->find($id);
            $this->assign($row);
            $this->display('add');
        }
    }

    /**
     * 添加供货商
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create('','register')===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->add()===false){
                $this->error(getError($this->_model));
            }
            $this->success('添加成功',U('index'));
        }else{
            $this->display();
        }
    }

    /**
     * 逻辑移除,
     * 物理删除
     * @param $id
     */
    public function remove($id)
    {
        $data=[
            'id'=>$id,
            'status'=>-1,
//            支持更复杂的查询情况例如：
//$map['id']  = array('in','1,3,8');可以改成：
//$map['id']  = array('exp',' IN (1,3,8) ');exp查询的条件不会被当成字符串，所以后面的查询条件可以使用任何SQL支持的语法，包括使用函数和字段名称。查询表达式不仅可用于查询条件，也可以用于数据更新
            'name'=>['exp','concat(name,"_del")'],
        ];
        if($this->_model->setField($data)===false){
            $this->error(getError($this->_model));
        }else{
            $this->success('移除成功',U('index'));
        }
    }

}