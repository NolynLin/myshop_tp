<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/13
 * Time: 20:58
 */

namespace Admin\Controller;


use Think\Controller;

class OrderInfoController extends Controller
{
    /**
     * @var \Admin\Model\OrderInfoModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('OrderInfo');
    }

    /**
     * 获取订单列表
     */
    public function index()
    {
        $rows=$this->_model->getList();
        $this->assign('rows',$rows);
        $this->assign('statues',$this->_model->statues);
        $this->display();
    }

    /**
     * 根据订单id,发货
     * @param $id
     */
    public function send($id)
    {
        if(IS_POST){
            if($this->_model->where(['id'=>$id])->setField('status',3)===false){
                $this->error(getError($this->_model));
            }
            $this->success('发货成功',U('index'));
        }else{
        $row=['id'=>$id];
        $this->assign($row);
        $this->display();
        }
    }
}