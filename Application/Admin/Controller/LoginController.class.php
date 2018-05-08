<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-type:text/html;charset='utf8';");
class LoginController extends Controller {
    //登陆主页
    public $table = "admin";
    public function index(){
        $this->display();
    }
    //登陆验证
    public function login(){
        if(!IS_POST)$this->error("非法訪問");
        $username =I('name','','addslashes');
        $password =I('password','','md5');
        /*$code = I('verify','','strtolower');
        //验证验证码是否正确
        if(!($this->check_verify($code))){
            $this->error('验证码错误');
        }*/
        //验证账号密码是否正确
        $user = M($this->table)->where("name = '%s' and password= '%s'",array($username,$password))->join('__ADMIN_ROLE__ ON __ADMIN__.role_id=__ADMIN_ROLE__.role_id','INNER')->find();
      //  sql($this->table);exit;

        if(!$user) {
            $this->error('帐号或密码错误 :(') ;
        }
        //验证账户是否被禁用
        if($user['status'] == 0){
            $this->error('帐号被禁用,请联系管理员 :(') ;
        }
       /* if($user['type'] == 1){
            $this->error('您没权限登陆后台 :(') ;
        }*/
        //验证是否为管理员
        //更新登陆信息
        $data =array(
            'last_time' => date('Y-m-d H:i:s',time()),
            'login_ip' => get_client_ip(),
        );
        //如果数据更新成功  跳转到后台主页
        $re = M($this->table)->where("admin_id =" .$user['admin_id'])->save($data);
        $map['visible'] = 1;
        if($re){
            if($user['act_list']=='all'){
                $list = M('system_module')->where($map)->order('orderby')->select();
            }
            else{
                $map['mod_id']  = array('in',$user['act_list']);
                $list = M('system_module')->where($map)->order('p_id,orderby')->select();
            }
           // sql('system_module');
            //把二级放到一级下面
         //   dd($list);
            foreach($list as $k=>$v){
                if($v['level'] == 1){
                    $arr[$v['mod_id']] =$v;
                }
                if($v['level'] == 2){
                    $arr[$v['p_id']]['child'][] = $v;
                }
                unset($list[$k]);
            }
            $re = M('store')->where(['admin_id'=>$user['admin_id']])->find();
            $store_id = $re?$re['store_id']:0;
            session('adminId',$user['admin_id']);
            session('name',$user['admin_name']);
            session('store_id',$store_id);
            session('act_list',$arr);
            //查询此id是否为店铺

          //  dd($_SESSION);
            addLog(session('name').'登陆后台',session('adminId'));
            $this->success("登陆成功",U('Index/index'));
        }
        //定向之后台主页
    }
    //验证码
    public function verify(){
        $Verify = new \Think\Verify();
        $Verify->codeSet = '0123456789';
        $Verify->fontSize = 13;
        $Verify->length = 4;
        $Verify->entry();
    }
    protected function check_verify($code){
        $verify = new \Think\Verify();
        return $verify->check($code);
    }
    public function logout(){
        session(null);
        redirect(U('Login/index'));
    }
}