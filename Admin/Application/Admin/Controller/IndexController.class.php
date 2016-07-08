<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 13:21
 */

namespace Admin\Controller;


use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $this->display();
    }
    public function top(){
        $this->assign('row',login());
        $this->display();
    }
    public function menu(){
        $menu_model=D('Menu');
        $menu_lists=$menu_model->getMenuList();
        $this->assign('row',login());
        $this->assign('menu_lists',$menu_lists);
        $this->display();
    }
    public function main(){
        $this->display();
    }
}