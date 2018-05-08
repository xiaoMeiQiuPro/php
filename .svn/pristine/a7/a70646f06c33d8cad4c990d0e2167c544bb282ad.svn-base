<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-type:text/html;charset='utf8';");
class BaseController extends Controller {
    public function _initialize(){
        $sid = session('adminId');
        //判断用户是否登陆
        if(!isset($sid)){
            redirect(U('Login/index'));
        }
        $this->assign('act_list',session('act_list'));
    }
}