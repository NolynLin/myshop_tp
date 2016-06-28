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
    /**
     * @var \Admin\Model\ArticleModel
     */
    private $_model=null;
    protected function _initialize(){
        $this->_model=D('Article');
    }
    /**
     * 获取分页数据 并支持搜索
     */
    public function index()
    {
        //获取搜索的关键字model层需要做具体查询，这里用`article`.这样的方式
        $name=I('get.name');
        $cond=[];
        if(!empty($name)){
            $cond['name']=['like','%'.$name.'%'];
        }
        //绑定文章分类的
        $artCatModel = D('ArticleCategory');
        $artCats             = $artCatModel->getList();
        $this->assign('artCats',$artCats);
        //查询数据,分页,搜索
        $rows=$this->_model->getArtPage($cond);
        $this->assign($rows);
        $this->display();
    }
    /**
     * 添加文章标题
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create('','register')===false){
                $this->error(getError($this->_model));
            }
            if($this->_model->addArtCat(I('post.content'))===false){
                $this->error('添加失败');
            }else{
                $this->success('添加成功',U('index'));
            }
        }else{
            /**
             * 显示出可选择的文章分类,这里要将状态大于=0的显示出来,所以就在文章分类的一个方法里写了一个获取能显示的方法.
             */
            $artCatModel = D('ArticleCategory');
            $rows             = $artCatModel->getList();
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
            if($this->_model->saveAriCon(I('post.'))===false){
                $this->error('修改失败');
            }else{
                $this->success('修改成功',U('index'));
            }
        }else{
            //1.回显文章标题详情
            $row=$this->_model->find($id);
            $this->assign($row);
            //2.显示文章分类列表
            $artCatModel = D('ArticleCategory');
            $rows             = $artCatModel->getList();
            $this->assign('rows',$rows);
            //3.显示文章具体内容
            $artContent=M('ArticleContent');
            $data=$artContent->where(['article_id'=>$id])->find();
            $this->assign($data);
            $this->display('add');
        }
    }
    /**
     * 物理删除文章
     * @param $id
     */
    public function remove($id)
    {
        if($this->_model->removeArt($id)===false){
            $this->error('删除失败');
        }else{
            $this->success('移除成功',U('index'));
        }
    }

}