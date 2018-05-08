<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/26
 * Time: 19:54
 * 區域管理
 */
namespace Admin\Controller;
use Admin\Controller;
class AreaController extends BaseController
{
    /**項目列表*/
    public $tab ='area';
    public function __construct()
    {
        parent::__construct();
    }
    public function index($key="")
    {
        if($key !== ""){
            $where['name'] = array('like', "%$key%");
        }
        else{
            $where=array();
        }
        $count  = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow.','.$Page->listRows)->where($where)->
        order('pid')->
        /*join('admin_role ON admin.role_id=admin_role.role_id','INNER')->*/select();
        $index=$Page->firstRow;//显示序号
        foreach($list as $k=>$v){
            if($v['pid'] == 0){
                $list[$k]['p_name'] = '一級區域';
            }
            else{
                $list[$k]['p_name'] = M($this->tab)->where(array('area_id'=>$v['pid']))->find()['name'];
            }
        }
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('');
    }

    /**
     * 添加区域
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            //查询一级区域

            $this->assign("area",M($this->tab)->where(array('pid'=>0))->select());
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据则添加到表
            if(M($this->tab)->where(array('name'=>I('post.name')))->find()){
                echo "<script>alert('區域名已存在');history.go(-1)</script>";
                exit;
            }
            $_POST['level'] = 2;
            if(I("post.pid") == 0){
                $_POST['level'] = 1;
            }
            $model = M($this->tab)->add(I('post.'));

            if ($model) {
                addLog(session('name').'添加區域',session('adminId'));
                $this->success("添加區域成功", __CONTROLLER__."/index",1);
            }
            else {
                $this->error("数据异常,添加失败",__CONTROLLER__."/index",2);
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
        if (!IS_POST) {
            $map['area_id'] = I("get.id");
            $model = M($this->tab)/*->join('admin_role ON admin.role_id = admin_role.role_id')*/->where($map)->find();
            $this->assign('data',$model);
          //  dd($model);exit;
            $this->assign("area",M($this->tab)->where(array('pid'=>0))->select());
            $this->display();
        }
        //修改信息
        if (IS_POST) {
            $map['area_id'] = I("post.area_id");
            $_POST['level'] = 2;
            if(I("post.pid") == 0){
                $_POST['level'] = 1;
            }
         //   dd($_POST);exit;
            $name=M($this->tab)->where(array('area_id'=>array('not in',I("post.area_id")),'name'=>I("post.name")))->find();

            if($name) {
                echo "<script>alert('此區域名字已經存在');history.go(-1)</script>";
                exit;
            }
            $model = M($this->tab)->where($map)->save(I('post.'));
          //  sql($this->tab);exit;
            if ($model) {
                addLog(session('name').'修改區域名稱',session('adminId'));
                $this->success("修改成功", __CONTROLLER__."/index",1);
            }
            else {
                $this->error("数据异常,失败",__CONTROLLER__."/index",2);
                exit;
            }
        }

    }
    /**
     * 删除
     */
    public function del()
    {
        $id = $_GET['id'];
        if(M('project')->where('area_id2 ='.$id)->find()){
            $this->error("此區域下有項目存在,請先刪除項目", __CONTROLLER__."/index",3);
        }
        if(M($this->tab)->where('pid ='.$id)->find()){
            $this->error("此區域下還有下級區域,請先刪除二級區域", __CONTROLLER__."/index",3);
        }
        $map['area_id']  = array('in',$id);
        $result = M($this->tab)->where($map)->delete();
        if($result){
            addLog(session('name').'删除區域',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/index",2);
        }else{
            $this->error("删除失败");
        }
    }
    //ajax修改状态
    function ajax_status(){
        //$map['admin_id'] = $_POST['id'];
        $map['status'] = $_POST['status'];
        $re=M($this->tab)->where('admin_id = '.$_POST['id'])->save($map);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }

}



