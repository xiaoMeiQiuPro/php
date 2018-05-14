<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/26
 * Time: 16:42
 */

namespace Admin\Controller;
use Admin\Controller;
class GoodsController extends BaseController
{

    public $tab = 'goods';

    public function __construct()
    {
        parent::__construct();
    }
    /**商品列表*/
    public function index($key = "")
    {
        if ($key !== "") {
            $where['goods_name|goods_sn'] = array('like', "%$key%");
            $where['_logic'] = "OR";
        } else {
            $where = array();
        }
        $count = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->
            join("__CAT__   ON __CAT__.cat_id = __GOODS__.cat_id",'LEFT')->
        field("tp_goods.*,tp_cat.name")->
        select();
        // dd($list);exit;
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('Goods/index');
    }
    /**
     * 添加商品
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $this->assign('cat',M('cat')->select());
            $this->display();
        }
        if (IS_POST) {
            $_POST['original_img'] =  $_POST['goods_img'][0];
            $_POST['goods_img'] = implode(',',$_POST['goods_img']);
            $_POST['info_img'] = implode(',',$_POST['info_img']);
            $_POST['goods_sn']  = time()+rand(1000,9999);
            $_POST['on_time']  = time();
            $re =  M("goods")->add($_POST);
            if($re){
               echo 1;
            }else{
                echo 2;
            }

        }
    }

    public function comment_del()
    {
        $id = $_GET['id'];
        $map['id']  = array('in',$id);
        $result = M('project_comment')->where($map)->delete();
        if($result){
            addLog(session('name').'删除系統消息評論',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/index",2);
        }else{
            $this->error("删除失败");
        }
    }
    /*評論列表*/
    function comment($key = ''){
        $map['project_id'] = $_GET['id'];
        $count  = M('project_comment')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('project_comment')->limit($Page->firstRow.','.$Page->listRows)->where($map)->
        join("user ON project_comment.user_id = user.user_id","LEFT")->
        field('project_comment.*,user.china_name')->
        order('time desc')
            ->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('project/comment');
    }
    /**
     * 更新
     *
     */
    public function update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $map['goods_id'] = $_GET['id'];
            $list = M($this->tab)->where($map)->find();
            //分类
            $cat = M('cat')->select();
            $this->assign('cat', $cat);
            //商品图片
            $goods_img  =    explode(',',$list['goods_img']);
            $this->assign('goods_img',$goods_img);
            $info_img  =    explode(',',$list['info_img']);

            $this->assign('info_img',$info_img);
            //商品详情图

            $this->assign('list', $list);
            $this->display();
        }
        //修改信息
        if (IS_POST) {
            $_POST['original_img'] =  $_POST['goods_img'][0];
            $_POST['goods_img'] = implode(',',$_POST['goods_img']);
            $_POST['info_img'] = implode(',',$_POST['info_img']);
            $re =  M("goods")->where(['goods_id'=>$_POST['goods_id']])->save($_POST);
            if($re!==false){
                echo 1;
            }
            else{
                echo 2;
            }

        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $map[$_POST['id_key']]  = array('in',$_POST['id']);

        //查看订单是否有此商品
        if(M("order")->where($map)->find()){
            echo "3";die;
        }
        $result = M($this->tab)->where($map)->delete();
        if ($result) {
            echo 1 ;
        } else {
           echo 2;
        }
    }

    //ajax修改状态
    function ajax_status(){
        $map[$_POST['id_key']] = $_POST['id'];
        $val[$_POST['status_key']] = $_POST['status'];
        $re=M('goods')->where($map)->save($val);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
    //請求二級區域
    function ajax(){
        $re = M("area")->where('pid = '.$_POST['pid'])->select();
        echo  json_encode($re);
    }
    function map(){
        //google ajax請求
        $address = !empty($_POST['address'])?$_POST['address']:"成都";// Google HQ
        $prepAddr = str_replace(' ','+',$address);

        $geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&language=zh-TW&sensor=false');
        print_r($geocode);
        $output= json_decode($geocode);

        $lat = $output->results[0]->geometry->location->lat;
        $lng = $output->results[0]->geometry->location->lng;

        echo  $lat   ;  echo  $lng;
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
}