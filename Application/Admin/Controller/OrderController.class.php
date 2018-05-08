<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/16
 * Time: 17:05
 */
namespace Admin\Controller;
use Admin\Controller;
class OrderController extends BaseController
{
    public $tab = 'order';

    public function __construct()
    {
        parent::__construct();
    }
    /**未发货列表*/
    public function index($key = "")
    {
        if($_GET['start'] && $_GET['end']){
            $start = strtotime($_GET['start']);
            $end = strtotime($_GET['end']);
            $where['pay_time'] = array(array('egt',$start),array('elt',$end)) ;
        }
        $where['order_status'] = 2;
        $count = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->
            alias("a")->
            join("__GOODS__ b  on  a.goods_id = b.goods_id","LEFT")->
            order("order_id desc")->
            field('a.*,b.goods_name')->
            select();
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('');
    }

    /**已发货列表*/
    public function fh_index($key = "")
    {
        if($_GET['start'] && $_GET['end']){
            $start = strtotime($_GET['start']);
            $end = strtotime($_GET['end']);
            $where['shipping_time'] = array(array('egt',$start),array('elt',$end)) ;
        }
        $where['order_status'] = 3;
        $count = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->
        alias("a")->
        join("__GOODS__ b  on  a.goods_id = b.goods_id","LEFT")->
        order("order_id desc")->
        field('a.*,b.goods_name')->
        select();
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('');
    }

    /**已完成列表*/
    public function wc_index($key = "")
    {
        if($_GET['start'] && $_GET['end']){
            $start = strtotime($_GET['start']);
            $end = strtotime($_GET['end']);
            $where['confirm_time'] = array(array('egt',$start),array('elt',$end)) ;
        }
        $where['order_status'] = 4;
        $count = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->
        alias("a")->
        join("__GOODS__ b  on  a.goods_id = b.goods_id","LEFT")->
        order("order_id desc")->
        field('a.*,b.goods_name')->
        select();
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('');
    }

    /**退款列表*/
    public function refund_index($key = "")
    {
        if($_GET['start'] && $_GET['end']){
            $where['pay_time'] = array(array('egt',$_GET['start']),array('elt',$_GET['end'])) ;
        }
        $where['order_status'] = 5;
        $count = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->
        alias("a")->
        join("__GOODS__ b  on  a.goods_id = b.goods_id","LEFT")->
        order("order_id desc")->
        field('a.*,b.goods_name')->
        select();
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('');
    }
    /**
     * 添加
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            // $this->assign('role', M('admin_role')->select());
            $this->display();
        }
        if (IS_POST) {
            //  dd($_POST);dd($_FILES);exit;
            $model = M($this->tab)->add(I('post.'));
            if ($model) {
                echo 1;exit;
            }else {
                echo  '数据异常';
                exit;
            }
        }

    }

    /**
     * 更新
     *
     */
    public function update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $map['ad_id'] = I("get.id");
            $model = M($this->tab)->where($map)->find();
            $this->assign('list', $model);
            $this->display();
        }
        //修改信息
        if (IS_POST) {
            $map['ad_id'] = I("post.ad_id");
            $re = M($this->tab)->where($map)->save($_POST);
            // print_r($_POST);
            if ($re) {
                echo 1;
            } else {
                echo '数据异常';
            }
        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $id = $_POST['id'];
        $map[$_POST['id_key']] = array('in', $id);
        $result = M($this->tab)->where($map)->delete();
        if ($result) {
            echo 1;
        } else {
            echo '数据异常';
        }
    }
    //ajax修改状态
    function ajax_status(){
        //$map['admin_id'] = $_POST['id'];
        $map['status'] = $_POST['status'];
        $re=M($this->tab)->where('ad_id = '.$_POST['id'])->save($map);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
    function upload(){
        if(!empty($_FILES['file']['name'])){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     10145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg',  'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
            $upload->savePath  =     '/goods_img/'; // 设置附件上传（子）目录
            // 上传文件
            $info   =   $upload->upload();

            if(!$info) {// 上传错误提示错误信息
                //return $upload->getError();
                return  $upload->getError();exit;
            }else{// 上传成功
                // $this->success('上传成功！');
                foreach($info as $file){
                    // echo json_encode($file);die;
                    $url = __ROOT__.'/Uploads'.$file['savepath'].$file['savename'];//路径
                }
                echo $url;
            }
        }
    }
    function status(){
        $map[$_POST['id_key']] = $_POST['id'];
        $val[$_POST['status_key']] = $_POST['status'];
        //发货修改发货时间
        if($_POST['status'] == 3){
            M('order')->where($map)->save(['shipping_time'=>time()]);
        } //确认收货时间
        elseif($_POST['status'] == 4){
            M('order')->where($map)->save(['confirm_time'=>time()]);
        }
        $re=M('order')->where($map)->save($val);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
}