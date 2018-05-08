<?php
namespace Admin\Controller;
use Admin\Controller;

class IndexController extends BaseController{

    public function index(){
       $list = M('admin')->
       join('__ADMIN_ROLE__ ON __ADMIN__.admin_id=__ADMIN_ROLE__.role_id', 'LEFT')->
       where(array('admin_id'=>$_SESSION['adminId']))->find();
        $this->assign('list',$list);
        $this->assign('act_list',session('act_list'));
        $this->display();
    }
    function welcome(){
        $this->display();
    }
}
