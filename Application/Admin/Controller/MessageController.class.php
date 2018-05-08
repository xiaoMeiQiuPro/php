<?php
namespace Admin\Controller;
use Admin\Controller;
/*** Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/22
 * Time: 17:05
 * 消息管理
 */
class MessageController extends BaseController
{
    /**----------------系统消息列表----------------------*/
    public $tab ='admin_notice';
    public function __construct()
    {
        parent::__construct();
    }
    public function system_index($key="")
    {
        if($key !== ""){
            $where['title'] = array('like', "%$key%");
        }
        else{
            $where=array();
        }
        $count  = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow.','.$Page->listRows)->where($where)->
        join('admin ON admin.admin_id=admin_notice.admin_id','LEFT')->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index',$index);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('message/system/index');
    }
    /*代理動態消息列表*/
    public function agent_index($key="")
    {
        if($key !== ""){
            $where['title'] = array('like', "%$key%");
        }
        else{
            $where=array();
        }
        $count  = M('user_notice')->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();//分页显示输出
        $list = M('user_notice')->limit($Page->firstRow.','.$Page->listRows)->
        where($where)->
        join('user ON user.user_id=user_notice.user_id','LEFT')->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('message/agent/index');
    }
    /**
     * 添加消息
     */
    public function system_add()
    {
        //默认显示添加表单
        if (!IS_POST) {
           // $this->assign('role',M('admin_role')->select());
            $this->display('message/system/add');
        }
        if (IS_POST) {
            //如果用户提交数据则添加到表
            $_POST['img_url'] = implode(',',$_POST['img_url']);
            $_POST['date'] = get_date();
            $_POST['admin_id'] = session('adminId');
            $model = M($this->tab)->add($_POST);
            if ($model) {
                addLog(session('name').'添加系統消息',session('adminId'));
                $this->success("添加成功", __CONTROLLER__."/system_index",1);
            }
            else {
                $this->error("数据异常,添加失败",'',2);
                exit;
            }
        }
    }
    /**
     * 更新
     *
     */
    public function system_update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $map['admin_notice_id'] = I("get.id");
            $model = M($this->tab)->where($map)->find();
            $img = explode(',',$model['img_url']);
            $this->assign('img',$img);
            $this->assign('list',$model);
            $this->display('message/system/update');
        }
        //修改信息
        if (IS_POST) {
            $_POST['img_url'] = implode(',',$_POST['img_url']);
            $map['admin_notice_id'] = $_POST['admin_notice_id'];
            $re=M($this->tab)->where($map)->save($_POST);
            if ($re) {
                addLog(session('name').'修改系統消息',session('adminId'));
                $this->success("更新成功",  __CONTROLLER__."/system_index",1);
            }
            else {
                $this->error("更新失败", __CONTROLLER__."/system_index",2);
            }
        }

    }
    public function agent_update()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $map['user_notice_id'] = I("get.id");
            $model = M('user_notice')->where($map)->find();
            $img = explode(',',$model['img_url']);
            $this->assign('img',$img);
            $this->assign('list',$model);
            $this->display('message/agent/update');
        }
        //修改信息
        if (IS_POST) {
            $_POST['img_url'] = implode(',',$_POST['img_url']);
            $map['user_notice_id'] = $_POST['user_notice_id'];
            $re=M('user_notice')->where($map)->save($_POST);
            if ($re) {
                addLog(session('name').'修改代理動態',session('adminId'));
                $this->success("更新成功",  __CONTROLLER__."/agent_index",1);
            }
            else {
                $this->error("更新失败", __CONTROLLER__."/agent_index",2);
            }
        }

    }
    /**
     * 删除評論
     */
    public function system_del()
    {
        $id = $_GET['id'];
        $map['id']  = array('in',$id);
        $result = M('admin_notice_comment')->where($map)->delete();
        if($result){
            addLog(session('name').'删除系統消息評論',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/system_index",2);
        }else{
            $this->error("删除失败");
        }
    }
    public function agent_del()
    {
        $id = $_GET['id'];
        $map['id']  = array('in',$id);
        $result = M('user_notice_comment')->where($map)->delete();
        if($result){
            addLog(session('name').'删除代理動態消息評論',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/agent_index",2);
        }else{
            $this->error("删除失败");
        }
    }
    /**
     * 删除動態
     */
    public function system_del1()
    {
        $id = $_GET['id'];
        $map['admin_notice_id']  = array('in',$id);
        $map1['notice_id']  = array('in',$id);
        $result = M('admin_notice')->where($map)->delete();
        M('admin_notice_comment')->where($map1)->delete();
        if($result){
            addLog(session('name').'删除系統消息',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/system_index",2);
        }else{
            $this->error("删除失败");
        }
    }
    public function agent_del1()
    {
        $id = $_GET['id'];
        $map['user_notice_id']  = array('in',$id);
        $map1['notice_id']  = array('in',$id);
        $result = M('user_notice')->where($map)->delete();
        M('user_notice_comment')->where($map1)->delete();
        if($result){
            addLog(session('name').'删除代理動態消息',session('adminId'));
            $this->success("删除成功", __CONTROLLER__."/agent_index",2);
        }else{
            $this->error("删除失败");
        }
    }
    //ajax修改状态
    function ajax_status(){
        //$map['admin_id'] = $_POST['id'];
        $map['is_top'] = $_POST['is_top'];
        $map1['is_top'] = 0;
        $re1 = M($this->tab)->where('is_top = 1')->save($map1);
        $re=M($this->tab)->where('admin_notice_id = '.$_POST['id'])->save($map);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
    //代理動態置頂

    function ajax_status_agent(){
        //$map['admin_id'] = $_POST['id'];
        $map['is_top'] = $_POST['is_top'];
        $map1['is_top'] = 0;
        $re1 = M('user_notice')->where('is_top = 1')->save($map1);
        $re=M('user_notice')->where('user_notice_id = '.$_POST['id'])->save($map);
        if($re){
            echo  1;
        }
        else{
            echo 0;
        }
    }
    //多图上传
    function upload(){
        if(!empty($_FILES['file']['name'])){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     10145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg',  'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
            $upload->savePath  =     '/admin_notice/'; // 设置附件上传（子）目录
            // 上传文件
            $info   =   $upload->upload();

            if(!$info) {// 上传错误提示错误信息
                //return $upload->getError();
                return  $upload->getError();exit;
            }else{// 上传成功
                // $this->success('上传成功！');
                foreach($info as $file){
                    // echo json_encode($file);die;
                    $url = __ROOT__.'/Uploads'.$file['savepath'].$file['savename'];//头像路径
                }
                echo $url;
            }
        }
    }
    /*評論列表*/
    function system_comment($key = ''){
        $map['notice_id'] = $_GET['id'];
        $count  = M('admin_notice_comment')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('admin_notice_comment')->limit($Page->firstRow.','.$Page->listRows)->where($map)->
            join("user ON admin_notice_comment.user_id = user.user_id","LEFT")->
            field('admin_notice_comment.*,user.china_name')->
        order('time desc')
            ->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('message/system/comment');
    }
    function agent_comment($key = ''){
        $map['notice_id'] = $_GET['id'];
        $count  = M('user_notice_comment')->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('user_notice_comment')->limit($Page->firstRow.','.$Page->listRows)->where($map)->
        join("user ON user_notice_comment.user_id = user.user_id","LEFT")->
      //  field('user_notice_comment.*,user.china_name')->
        order('time desc')
            ->select();

        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('message/agent/comment');
    }


}


