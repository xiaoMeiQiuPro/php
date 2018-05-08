<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 管理員管理
 */
class AdminController extends BaseController
{
    /**管理員列表*/
    public $tab ='admin';
    public function __construct()
    {
        parent::__construct();
    }
    public function index($key="")
    {


        if($key !== ""){
            $where['admin_name'] = array('like', "%$key%");
        }
        else{
            $where=array();
        }
        $count  = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow.','.$Page->listRows)->where($where)->
        join('__ADMIN_ROLE__ ON __ADMIN__.role_id=__ADMIN_ROLE__.role_id','LEFT')->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('admin/index');
    }

    /**
     * 添加管理员
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $this->assign('role',M('admin_role')->where("role_id > 1")->select());
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据则添加到表
            if(M($this->tab)->where(array('name'=>I('post.name')))->find()){
             $this->ajaxReturn("此帐号已经存在");
             //   echo ;
               // echo "<script>layer.msg('此帐号已经存在', {icon: 2, time: 2000})</script>";
                exit;
            }
            else{
                $_POST['add_time'] = get_date();
                $_POST['password'] = md5(I('post.password'));
                $model = M($this->tab)->add(I('post.'));
            }
            if ($model) {
                addLog(session('name').'添加管理員',session('adminId'));
                echo 1;
                exit;
            }
            else {
                echo "服务器异常";
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

            $this->assign('role',M('admin_role')->where("role_id>1")->select());
            $map[$_GET['id_key']] = I("get.id");
            $model = M($this->tab)->join('__ADMIN_ROLE__ ON __ADMIN__.role_id = __ADMIN_ROLE__.role_id')->where($map)->find();
            $this->assign('list',$model);
            $this->display();
        }
        //修改信息
        if (IS_POST) {
          //  dd($_FILES);
            $name=M($this->tab)->where(array('admin_id'=>array('not in',I("post.admin_id")),'name'=>I("post.name")))->find();
            if($name) {
                echo "此帐号已经存在";
                exit;
            }
            //密碼為空則不修改
            if(I("post.password") == ''){
                unset($_POST['password']);
            }
            else{
                $_POST['password'] = md5(I("post.password"));
            }

            $map['admin_id'] = I("post.admin_id");
            $re=M($this->tab)->where($map)->save($_POST);
            // print_r($_POST);

            if ($re) {
                addLog(session('name').'修改管理员',session('adminId'));
                echo 1;

            }
            else {
                echo "修改失败";
            }
        }

    }
    /**
     * 删除
     */
    public function del()
    {
        $map[$_POST['id_key']]  = array('in',$_POST['id']);
        $result = M($this->tab)->where($map)->delete();
        if($result){
            addLog(session('name').'删除管理員',session('adminId'));
            echo 1;
        }else{
            echo 2;
        }
    }
    //ajax修改状态
    function ajax_status(){
        $map[$_POST['id_key']] = $_POST['id'];
        $val[$_POST['status_key']] = $_POST['status'];
        $re=M($this->tab)->where($map)->save($val);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
    //-----------管理員日誌---------------//
    public function log_index()
    {
        if($_GET['start'] && $_GET['end']){
            $where['date'] = array(array('egt',$_GET['start']),array('elt',$_GET['end'])) ;
        }
        else{
            $where=array();
        }
        $count  = M('admin_log')->join("__ADMIN__ a ON a.admin_id = __ADMIN_LOG__.admin_id","LEFT")->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('admin_log')->
        join("__ADMIN__ a ON a.admin_id = __ADMIN_LOG__.admin_id","LEFT")->
        limit($Page->firstRow.','.$Page->listRows)->order('admin_log_id desc')->where($where)
            ->field("tp_admin_log.*,a.admin_name,a.phone")
            ->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('admin/admin_log/index');
    }
}


