<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 12:21
 */
namespace Admin\Controller;
use Think\Controller;
class ArticleController extends Controller
{
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Article');
    }

    public function index()
    {
        
        $this->display();
    }

    public function add()
    {

    }

    public function edit($id)
    {

    }

    public function remove($id)
    {

    }

}