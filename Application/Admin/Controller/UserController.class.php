<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/10
 * Time: 17:48
 * Module: 会员管理
 */
namespace Admin\Controller;
use Admin\Controller;

class UserController extends BaseController
{

    public $tab ='users';
    public function __construct()
    {
        parent::__construct();
    }
    //-------------------------普通會員管理---------------------------------------
    public function index1($key="")
    {
        if($key !== ""){
          //  $where['email'] = array('like',"%$key%");
            $where['mobile'] = array('like',"%$key%");
            $where['name'] = array('like',"%$key%");
            $where['_logic'] = 'OR';
            $map["_complex"] = $where;
        }
        $count  = M($this->tab)->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow.','.$Page->listRows)->where($map)->
            order('last_login desc')->
        /*join('admin_role ON admin.role_id=admin_role.role_id','INNER')->*/select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('User/User1/index');
    }

    //查看亲子信息
    function child(){
        $this->assign('list',M('children')->where(['user_id'=>I('id')])->select());
        $this->display('User/User1/child');
    }


    //-----------用戶操作日誌---------------//
    public function log_index($key='',$key2='')
    {
        if($_POST['start'] && $_POST['end']){
            $where['date'] = array(array('egt',$_POST['start']),array('elt',$_POST['end'])) ;
        }
        else{
            $where=array();
        }
        $count  = M('user_log')->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('user_log')->limit($Page->firstRow.','.$Page->listRows)->order('user_log_id desc')->where($where)->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('User/User_log/index');
    }
    //-------------------------普通會員管理---------------------------------------
    public function vip_index($key="")
    {
        if($key !== ""){
            $map['name'] = array('like',"%$key%");
        }
        $count  = M('vip')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('vip')->limit($Page->firstRow.','.$Page->listRows)->where($map)->
        /*join('admin_role ON admin.role_id=admin_role.role_id','INNER')->*/select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('user/vip/index');
    }
    /**
     * 添加会员
     */
    public function vip_add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $this->display('User/vip/add');
        }
        if (IS_POST) {

            //如果用户提交数据则添加到表
            if(M('vip')->where(array('name'=>I('post.name')))->find()){
                echo "<script>alert('代理名稱已經存在');history.go(-1)</script>";
                exit;
            }
            else{
                $model = M('vip')->add(I('post.'));
            }
            if ($model) {
                addLog(session('name').'添加代理等級',session('adminId'));
                $this->success("添加成功", __CONTROLLER__."/vip_index",1);
            }
            else {
                $this->error("数据异常,添加失败",__CONTROLLER__."/vip_index",2);
                exit;
            }
        }
    }
    /**
     * 更新
     *
     */
    public function vip_update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $map['vip_id'] = I("get.id");
            $model = M('vip')->/*join('admin_role ON admin.role_id = admin_role.role_id')->*/where($map)->find();
            // dd($model);exit;
            $this->assign('list',$model);
            $this->display('User/vip/update');
        }
        //修改信息
        if (IS_POST) {
            $map['vip_id'] = I("post.vip_id");
            $name=M('vip')->where(array('vip_id'=>array('not in',I("post.vip_id")),'name'=>I("post.name")))->find();
            if($name) {
                echo "<script>alert('代理名字已经存在');history.go(-1)</script>";
                exit;
            }
            $re = M('vip')->where($map)->save($_POST);

            if ($re){
                addLog(session('name').'修改代理會員等級',session('adminId'));
                $this->success("更新成功",  __CONTROLLER__."/vip_index",1);
            }
            else {
                $this->error("更新失败", __CONTROLLER__."/vip_index",2);
            }
        }
    }
    /**
     * 删除
     */
    public function vip_del()
    {
        $id = $_GET['id'];
        $map['vip_id']  = array('in',$id);
        if(M('user')->where($map)->find()){
            echo "<script>alert('此代理等級下還有會員!請先刪除會員');history.go(-1)</script>";
            exit;
        }
        $result = M('vip')->where($map)->delete();
        if($result){
            addLog(session('name').'删除代理等級',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/vip_index",2);
        }else{
            $this->error("删除失败");
        }
    }
    //代理審核~~~~~~~~~~~~/////
    function check_user($key =''){
        $id = 2;
        if($_GET['id']!=''){
            $map['agent_examine.type'] =$_GET['id'];
            $id = $_GET['id'];
        }
        $this->assign('id',$id);
        $count  = M('agent_examine')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('agent_examine')->limit($Page->firstRow.','.$Page->listRows)->where($map)
            ->join('user ON agent_examine.user_id=user.user_id','LEFT')->
        join('vip ON agent_examine.vip_id=vip.vip_id','LEFT')->
            order('agent_examine.type')->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
       /* //已審核~
        $map1['agent_examine.type'] = 1;
        $count1  = M('agent_examine')->where($map1)->count();// 查询满足要求的总记录数
        $Page1 = new \Extend\Page($count1,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show1 = $Page->show();// 分页显示输出
        $list1 = M('agent_examine')->limit($Page->firstRow.','.$Page->listRows)->where($map1)
            ->join('user ON agent_examine.user_id=user.user_id','LEFT')->
        join('vip ON agent_examine.vip_id=vip.vip_id','LEFT')->select();
        $index1=$Page1->firstRow;//显示序号
        $this->assign('index1', $index1);
        $this->assign('list1', $list1);
        $this->assign('page1',$show1);
        $this->assign('count1',$count1);*/
        $this->display('User/Check_user/index');
    }
    //審核
    function user_sh(){

        if(!IS_POST){
            $map['id'] = I("get.id");
            $list = M('agent_examine')->where($map)
                ->join('user ON agent_examine.user_id=user.user_id','LEFT')->
                join('vip ON agent_examine.vip_id=vip.vip_id','LEFT')->find();
            $this->assign('list',$list);
            $this->display('User/Check_user/update');
        }
        //提交審核
        else{
            $map['id'] = I('post.id');
            if(I('post.yes')){
              $re = M('agent_examine')->where($map)->save(array('remark'=>I("post.remark"),'agent_status'=>1,'type'=>1));
            }
            else{
                $re = M('agent_examine')->where($map)->save(array('remark'=>I("post.remark"),'agent_status'=>0,'type'=>1));
            }
            if($re){
                $this->success("審核成功",  __CONTROLLER__."/check_user",2);
            }

        }
    }

    function upload(){
            if(!empty($_FILES['file']['name'])){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     10145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg',  'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
            $upload->savePath  =     '/store_img/'; // 设置附件上传（子）目录
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


//----------------------店铺管理---------------------------------
    function store(){
        if(session('adminId')!=1){
            $map['admin_id'] = session('adminId');
        }
        if($_GET['key']){
            $map['store_name'] = array('like',"%".$_GET['key']."%");;
        }
        $count  = M("store")->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M("store")->limit($Page->firstRow.','.$Page->listRows)->where($map)->
        join('__ADMIN__ ON __ADMIN__.admin_id=__STORE__.admin_id','LEFT')->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('User/Store/index');
    }
    function add_store(){
        if(!IS_POST){
            $this->display('user/Store/add');
        }
        else{
            if(M('admin')->where(['name'=>I('post.name')])->find()){
                echo "登陆帐号已存在";exit;
            }
            if(M('store')->where(['store_name'=>I('post.store_name')])->find()){
                echo "店铺名已存在";exit;
            }
            $admin = M("admin");
            //事务开启
            $admin->startTrans();

            //添加到数据表
            $admin_info = array(
                'name' => I('post.name'),
                'admin_name' => I('post.admin_name'),
                'add_time' => get_date(),
                'password' => md5(I('post.password')),
                'phone' =>I('post.phone'),
            );
            $id  = M('admin')->add($admin_info);
            $map = array(
                'store_name'=>I('post.store_name'),
                'store_desc'=>I('post.store_desc'),
                'store_pic'=>I('post.store_pic'),
                'store_time'=>time(),
                'admin_id' => getId('admin')
            );
            $store= M('store')->add($map); // 保存用户信息


            if ($id && $store){
                // 提交事务
                echo 1;
                $admin->commit();
                exit;
            }else{
                // 事务回滚
                echo '服务器异常';
                $admin->rollback();
            }
        }
    }
//修改状态
    function store_status(){
        $map[$_POST['id_key']] = $_POST['id'];
        $val[$_POST['status_key']] = $_POST['status'];
        $re=M('store')->where($map)->save($val);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
//修改资料
    public function store_update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $map[$_GET['id_key']] = I("get.id");
            $model = M('store')->where($map)->find();
            $this->assign('list',$model);
            $this->display('user/store/update');
        }
        //修改信息
        if (IS_POST) {
            //  dd($_FILES);
            $name=M('store')->where(array('store_id'=>array('not in',I("post.store_id")),'store_name'=>I("post.store_name")))->find();

            if($name) {
                echo "此店铺名已经存在";
                exit;
            }
            //密碼為空則不修改
            $map['store_id'] = I("post.store_id");
            $re=M('store')->where($map)->save($_POST);
            // print_r($_POST);

            if ($re) {
                addLog(session('name').'修改店铺资料',session('adminId'));
                echo 1;

            }
            else {
                echo "修改失败";
            }
        }

    }

}


