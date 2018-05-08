<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/31
 * Time: 17:11
 */
namespace Admin\Controller;
use Admin\Controller;
class AppointmentController extends BaseController
{

    public $tab = 'Appointment';

    public function __construct()
    {
        parent::__construct();
    }
    /**预约列表*/
    public function index($key = "")
    {
        /*if ($key !== "") {
           // $where['user_name'] = array('like', "%$key%");
            $where['tp_project_name'] = array('like', "%$key%");
            $where['_logic'] = 'OR';
            $map["_complex"] = $where;
        }*/
        $where['appointment.pid']=0;
        $count = M($this->tab)->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->
        join('user a ON appointment.agent_user_id=a.user_id','LEFT')->
        join('project b ON appointment.project_id=b.project_id','LEFT')->
        field("appointment.*,a.china_name user_name,b.china_name project_name")->
        where($where)->
        select();
        // dd($list);exit;
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('appointment/index');
    }
    /**
     * 添加項目
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $agent = $_GET['agent']?$_GET['agent']:'';
            $this->assign('agent',$agent);
            $this->assign('area', M('area')->where(array('pid'=>0))->select());
            $this->assign('cat', M('cat')->select());
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据则添加到表
            $_POST['head_img'] = uploadImg($_FILES['file-1']['name'],'project');
            if($_POST['img_url'] == '上传文件后缀不允许'){
                echo"<script>alert('上傳失敗,上傳格式僅支持jpg,gif, png, jpeg');history.go(-1)</script>" ;exit;
            }

            $model = M($this->tab)->add(I('post.'));
            if ($model) {
                addLog(session('name') . '添加項目', session('adminId'));
                $this->success("添加成功", __CONTROLLER__ . "/index", 1);
            } else {
                $this->error("数据异常,添加失败", __CONTROLLER__ . "/index", 2);
                exit;
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
            $map['project_id'] = $_GET['id']?$_GET['id']:'';
            $list = M($this->tab)->where($map)->find();
            $this->assign('area1', M('area')->where(array('pid'=>0))->select());
            $this->assign('area2', M('area')->where(array('pid'=>$list['area_id1']))->select());
            $this->assign('cat', M('cat')->select());
            $this->assign('list', $list);
            $this->display();
        }
        //修改信息
        if (IS_POST) {
            if(empty($_FILES['file-1']['name'])){
                unset($_POST['head_img']);
            }
            else{
                $_POST['head_img'] = uploadImg($_FILES['file-1']['name'],'project');
                if($_POST['head_img'] == '上传文件后缀不允许'){
                    echo"<script>alert('上傳失敗,上傳格式僅支持jpg,gif, png,jpeg');history.go(-1)</script>" ;exit;
                }
            }
            $map['project_id'] = I("post.project_id");
            $re = M($this->tab)->where($map)->save($_POST);
            // print_r($_POST);
            if ($re) {
                addLog(session('name') . '修改屋苑項目', session('adminId'));
                $this->success("更新成功", __CONTROLLER__ . "/index", 1);
            } else {
                $this->error("更新失败", __CONTROLLER__ . "/index", 2);
            }
        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $id = $_GET['id'];
        $map['appointment_id'] = array('in', $id);
        $result = M($this->tab)->where($map)->delete();
        if ($result) {
            addLog(session('name') . '删除預約單', session('adminId'));
            $this->success("删除成功", __CONTROLLER__ . "/index", 2);
        } else {
            $this->error("删除失败");
        }
    }
    //添加專屬會員
    function add_agent($key=''){
        if($key !== ""){
            $where['china_name'] = array('like',"%$key%");
            $where['english_name'] = array('like',"%$key%");
            $where['email'] = array('like',"%$key%");
            $where['phone'] = array('like',"%$key%");
            $where['_logic'] = 'OR';
            $map["_complex"] = $where;
        }
        $map['type'] = 2;
        $map['status'] = 1;
        $count  = M("user")->where($map)->count();// 查询满足要求的总记录数

        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M("user")->limit($Page->firstRow.','.$Page->listRows)->where($map)->
        join('vip ON vip.vip_id=user.vip_id','LEFT')->select();

        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display();
    }
    //查看协同代理会员
    function look_agent(){
        $map['user_id'] = $_GET['id']?array('in', $_GET['id']):'';
        $count  = M("user")->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M("user")->limit($Page->firstRow.','.$Page->listRows)->where($map)->
        join('vip ON vip.vip_id=user.vip_id','LEFT')->select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display();
    }
    //ajax修改状态
    function ajax_status(){
        //$map['admin_id'] = $_POST['id'];
        $map['status'] = $_POST['status'];
        $re=M($this->tab)->where('appointment_id = '.$_POST['id'])->save($map);
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
    function curl(){
        //初始化
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=香港&key=AIzaSyC7gVI5sfN98ZhqSzlksqmZ-mH5CGBZSbQ";
        //  $url="http://127.0.0.1/agent/";
        echo file_get_contents($url);
// 初始化一个 cURL 对象
        $curl = curl_init();
        /* $post_data = array ("address" => "香港","key" => "AIzaSyC7gVI5sfN98ZhqSzlksqmZ-mH5CGBZSbQ");
         // post数据
         curl_setopt($curl, CURLOPT_POST, 1);
         // post的变量　
         curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
         curl_setopt($curl, CURLOPT_URL, $url);*/
// 设置header
        curl_setopt($curl, CURLOPT_HEADER, 1);
// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
// 运行cURL，请求网页
        $data = curl_exec($curl);
        print_r($data);exit;
// 关闭URL请求
        curl_close($curl);
    }
}