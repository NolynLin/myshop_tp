<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 9:19
 */

namespace Admin\Controller;


use Think\Controller;

class BrandController extends Controller
{
    /**
     *
     * @var \Admin\Model\BrandModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Brand');
    }
    /**
     * 品牌显示页面 排除状态为-1的数据 分页显示 支持模糊查询
     */
    public function index()
    {
        //获取搜索的关键字
        $name=I('get.name');
        //拼接查询条件，传给model
        $cond=[];
        if(!empty($name)){
            $cond['name']=['like','%'.$name.'%'];
        }
        $rows=$this->_model->getRandData($cond);
        $this->assign($rows);
        $this->display();
    }
    /**
     * 根据id去修改品牌
     * @param $id
     */
    public function edit($id)
    {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(getError($this->_model));
            }
            dump($this->_model->data());exit;
            if($this->_model->save()===false){
                $this->error('修改失败');
            }else{
                $this->success('修改成功',U('index'));
            }
        }else{
            $row=$this->_model->find($id);
            $this->assign($row);
            $this->display('add');
        }
    }

    /**
     * 添加品牌 品牌名不能重复 排序不能为空
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create('','register')===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->add()===false){
                $this->error('添加失败');
            }else{
                $this->success('添加成功',U('index'));
            }
        }else{
            $this->display();
        }
    }

    /**
     * 根据id去移出品牌，做逻辑移出
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