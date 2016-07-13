<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/28
 * Time: 22:09
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class GoodsModel extends Model
{
    // 是否批处理验证
    protected $patchValidate = true;
    /**
     *
     */
    //自动验证
    protected $_validate = [
        ['name', 'require', '商品名称不能为空'],
        ['sn', '', '货号不能为空', self::VALUE_VALIDATE, 'unique'],
        ['goods_category_id', 'require', '商品分类不能为空'],
        ['brand_id', 'require', '商品品牌不能为空'],
        ['supplier_id', 'require', '供货商不能为空'],
        ['shop_price', 'require', '本店售价不能为空'],
        ['shop_price', 'currency', '本店售价格式不合法'],
        ['market_price', 'require', '市场售价不能为空'],
        ['market_price', 'currency', '市场售价格式不合法'],
        ['stock', 'require', '商品库存不能为空'],
        //这个验证规则无法验证提交过来的值为null的情况吗???是的，所以要在自动完成的时候写个回调函数，验证是否为null
//        ['goods_status', 'require', '商品推荐不能为空'],
    ];
    //自动完成
    protected $_auto = [
        //直接用self::MODEL_INSERT这个模式不行,要加register,为什么???
        ['inputtime', NOW_TIME, self::MODEL_INSERT],
        ['sn', 'createSn', self::MODEL_INSERT, 'callback'],
        //由于传过来的加入推荐是个数组,所以要处理,这里的加入推荐在修改的时候也可能更改,所以完成时间是任何情况
        ['goods_status', 'getGoodsStatus', self::MODEL_BOTH, 'callback'],
    ];

    /**
     * 当没选择商品是热销之类的时候，要处理一下
     * @param $goods_status
     * @return int|number
     */
    public function getGoodsStatus($goods_status)
    {
        if(isset($goods_status)){
            return array_sum($goods_status);
        }else{
            return 0;
        }
    }
    /**
     * 计算商品货号
     * @param $sn callback传过来的数据
     * @return string
     */
    public function createSn($sn)
    {
        $this->startTrans();
        //如果用户手动输入了商品编号,则不处理
        if ($sn) {
            return $sn;
        }
        //生成规则:SN年月日编号:SN2016062800001,即当天添加了多少个新商品,记录一个Num值,如果有值就加一,没有就从1开始计算
        //获取今天已经创建了多少商品
        $date = date('Ymd');
        $good_num_model = M('GoodsNum');
        //获取上传的num号
        $num = $good_num_model->getFieldByDay($date, 'num');
        //如果存在,就计算新的值,并保存到数据库
        if ($num) {
            ++$num;
            $data = ['day' => $date, 'num' => $num];
            $flags = $good_num_model->save($data);
        } else {
            $num = 1;
            $data = ['day' => $date, 'num' => $num];
            $flags = $good_num_model->add($data);
        }
        //如果保存失败,就回滚
        if ($flags === false) {
            $this->rollback();
        }
        //str_pad()使用另一个字符串填充字符串为指定长度
        $sn = 'SN' . $date . str_pad($num, 5, '0', STR_PAD_LEFT);
        return $sn;
    }

    /**
     * 添加数据,这里需要操作两张表,事务在计算货号那里已经开启了
     */
    public function addGoods()
    {
        //由于商品编辑时传了个隐藏域的id,添加和编辑是同一个html页面,所以在添加的时候不需要这个id,要删除
        unset($this->data['id']);
        //1.将商品基本数据添加到goods表
        if (($goods_id = $this->add()) === false) {
            $this->rollback();
            return false;
        }
        //2.将商品详细信息添加到goods_intro表
        //由于这里的操作和更新的时候类似,所以提出来封装一下由于有两种操作,所以有第二个参数进行判断,为真是添加,false是修改
        if($this->_save_goods_content($goods_id,true)===false){
            $this->rollback();
            return false;
        }
        //3.保存照片到goods_gallery表
        //调用保存图片的方法,因为和修改时保存图片的方法类似,所以封装起来
        //返回的值:如果有数据并且保存失败了,为1   ,此时应该回滚
        //         如果有数据,并且保存成功了,为0,此时不会滚滚
        if($this->_save_goods_gallery($goods_id)){
            $this->rollback();
            return false;
        }
        //添加会员价,存在字段才去操作
        if($this->_save_member_price($goods_id)===false){
            $this->error='保存会员价失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 保存会员价
     * @param $goods_id
     * @return bool|string
     */
    protected function _save_member_price($goods_id){
        //先删除原有的
        $member_goods_price_model=M('MemberGoodsPrice');
        $member_goods_price_model->where('goods_id='.$goods_id)->delete();
        if(I('post.member_level_price')){
            $data=[];
            foreach(I('post.member_level_price') as $key=>$member_pric){
                $data[]=[
                    'goods_id'=>$goods_id,
                    'member_level_id'=>$key,
                    'price'=>$member_pric
                ];
            }
            return $member_goods_price_model->addAll($data);
        }else{
            return '';
        }
    }

    /**
     * 获取显示数据
     */
    public function getGoodsPage(array $cond = [])
    {
        $cond = array_merge(['status' => 1], $cond);
        //获取总条数
        $count = $this->where($cond)->count();
        //获取分页代码
        $page_setting = C('PAGE_SETTING');
        $page = new Page($count, $page_setting['PAGE_SIZE']);
        //设置样式
        $page->setConfig('theme', $page_setting['PAGE_THEME']);
        $page_html = $page->show();
        //获取分页数据
        $rows = $this->where($cond)->page(I('get.p', 1), $page_setting['PAGE_SIZE'])->select();
        //由于列表页要展示是否是新品精品热销,但是这些信息放在一个字段中,所以为了简化视图代码,我们在模型中处理好后再返回
        foreach ($rows as $key => $row) {
            $row['is_best'] = $row['goods_status'] & 1 ? true : false;
            $row['is_new'] = $row['goods_status'] & 2 ? true : false;
            $row['is_hot'] = $row['goods_status'] & 4 ? true : false;
            $rows[$key] = $row;
        }
//        dump($rows);exit;
        return compact('rows', 'page_html');
    }

    /**
     * 根据id修改商品表,同时修改商品详细描述表,修改商品相册表
     */
    public function saveGoods($id)
    {
        $this->startTrans();
        //1.修改商品表
        if ($this->save() === false) {
            $this->rollback();
            return false;
        }

        //2.根据id去修改商品描述表
        //由于这里的操作和添加的时候类似,所以提出来封装一下,由于有两种操作,所以有第二个参数进行判断,为真是添加,false是修改
        if($this->_save_goods_content($id,false)===false){
            $this->rollback();
            return false;
        }
        //3.修改商品相册表
        //调用保存图片的方法,因为和添加商品时保存图片的方法类似,所以封装起来
        //返回的值:如果有数据并且保存失败了,为1   ,此时应该回滚
        //         如果有数据,并且保存成功了,为0,此时不会滚滚
        if($this->_save_goods_gallery($id)){
            $this->rollback();
            return false;
        }
        //4.,修改会员价
        if($this->_save_member_price($id)===false){
            $this->error='保存会员价失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function getGoodsInfo($id)
    {
        $row = $this->find($id);
        //由于在前端展示的时候,需要使用到各个状态,所以我们变成一个json对象
        $row['goods_status'];
        $tmp = [];
        if ($row['goods_status'] & 1) {
            $tmp[] = 1;
        }
        if ($row['goods_status'] & 2) {
            $tmp[] = 2;
        }
        if ($row['goods_status'] & 4) {
            $tmp[] = 4;
        }
//        在前端展示的时候,回显商品的推荐状态,使用json格式方便取得数据,这里转了一下
        $row['goods_status'] = json_encode($tmp);
        unset($tmp);
        //获取商品的详细信息
        $goodsintroModel = M('GoodsIntro');
        $row['content'] = $goodsintroModel->getFieldByGoodsId($id, 'content');
        //获取商品的图片地址
        $goodsgalleryModel=M('GoodsGallery');
        //这里要获取所有的path,所以参数要这样传,并且这里获取了id,后面删除图片的时候需要用到
        $paths=$goodsgalleryModel->getFieldByGoodsId($id,'id,path');
        $row['path']=$paths;
        //获取商品的会员价
        $member_goods_price_model=M('MemberGoodsPrice');
        $member_goods_price=$member_goods_price_model->where('goods_id='.$id)->getField('member_level_id,price');
        return  compact('row','member_goods_price');
    }

    /**
     * 保存商品详情的方法
     * @param $goods_id 对应商品id
     * @param $is_new   状态,是添加还是编辑
     * @return bool|mixed
     */
    public function _save_goods_content($goods_id, $is_new)
    {
        $data = [
            'goods_id' => $goods_id,
            'content' => I('post.content', '', false),
        ];
        $goods_intro_model = M('GoodsIntro');
        if ($is_new) {
            return $goods_intro_model->add($data);

        } else {
            return  $goods_intro_model->save($data);
        }
    }

    /**
     * 保存商品图片地址,因为不管是修改还是添加,都是要追加添加图片地址,所以这里就不分是是修改还是添加了了
     * @param $goods_id 对应商品的id
     * @return bool
     */
    public function _save_goods_gallery($goods_id)
    {
        $paths=I('post.path');
        $data=[];
        foreach($paths as $path){
            $data[]=['goods_id'=>$goods_id,'path'=>$path];
        }
        //如果上传了相册,并且相册保存失败,就回滚,如果没有上传相册,也不会因为插入失败而进入回滚
        $goods_gallery_model=M('GoodsGallery');
        return ($paths && ($goods_gallery_model->addAll($data)===false));
    }

    /**
     7* 逻辑删除商品7
     * @param $id
     * @return bool
     */

    public function removeGoods($id)
    {
        $data=[
          'id'=>$id,
          'name'=>['exp','concat(name,"_del")'],
          'status'=>0,
        ];
        return $this->setField($data);
    }
}