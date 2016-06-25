<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/25
 * Time: 12:32
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class ArticleModel extends Model
{
    // 是否批处理验证
    protected $patchValidate    =   true;
    protected $_validate = [
        //验证品牌名
        ['name','require','文章名称不能为空'],
        ['name','','文章名称已存在',self::EXISTS_VALIDATE ,'unique','register'],
        //验证排序
        ['sort','require','文章排序不能为空'],
        ['sort','number','文章排序只能是数字'],
        ['content','require','文章内容不能为空'],
    ];
    //自动完成规则
    protected $_auto=[
        ['inputtime',NOW_TIME,'register'],
    ];

    /**
     * 提交文章标题 同时添加文章内容 内容为必填
     * @param $data
     */
    public function addArtCat($content)
    {
        //开启事务
        $this->startTrans();
        //如果文章标题保存成功，继续执行将文章内容保存到内容表
        $artid=$this->add();
        if($artid===false){
            $this->rollback();
            return false;
        }
        $data=[
          'article_id'=>$artid,
          'content'=>$content,
        ];
        //取得文章表实例 添加文章内容到数据表
        $artCat=D('ArticleContent');
        if($artCat->add($data)===false){
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 获取分页和搜索数据
     * @param array $cond
     */
    public function getArtPage(array $cond)
    {
        //获取分页配置
        $page_setting=C('PAGE_SETTING');
        $count=$this->where($cond)->count();
        $page=new Page($count,$page_setting['PAGE_SIZE']);
        $page->setConfig('theme',$page_setting['PAGE_THEME']);
        $page_html=$page->show();
        //获取满足条件的数据
//        $row=$this->query('SELECT *,ac.name as aname FROM article AS ar JOIN article_category AS ac ON ar.`article_category_id`=ac.`id` WHERE ar.`status`>=0);sql语句
        //通过拼接的方式，从文章标题表查询出标题信息，再将对应的标题所属的文章分类的name查询出来，这里用到了连接查询，并且字段是article表中的所有字段，article_category表只需要name，那么直接用select，会根据主键去查找，有两个主键，会冲突，这里是先用select(false)获取到sql语句，再执行,这里用了联合查询，数据会被覆盖，所以只能按别名写
        $sql=$this->where($cond)->join('article_category AS ac ON `article`.`article_category_id`=ac.`id`')->page(I('get.p',1),$page_setting['PAGE_SIZE'])->field('`article`.`intro` as aintro,`article`.status as astatus ,`article`.name as aname,`article`.`id` as aid,`article`.`sort` as asort,ac.name')->select(false);
//        dump($row);
        $rows=$this->query($sql);
//        dump($rows);exit;
//        $rows=$this->where($cond)->page(I('get.p',1),$page_setting['PAGE_SIZE'])->select();
        return compact('rows','page_html');
    }
    /**
     * 删除数据
     */
    public function removeArt($id)
    {
        //开启事务
        $this->startTrans();
        //删除文章标题表的数据
        if($this->delete($id)===false){
            $this->rollback();
            return false;
        }
        //执行成功再删除文章的内容
        $artConModel=M('ArticleContent');
        if($artConModel->where(['article_id'=>$id])->delete()===false){
            $this->rollback();
            return false;
        }
        //执行成功则提交
        $this->commit();
        return true;
    }
}