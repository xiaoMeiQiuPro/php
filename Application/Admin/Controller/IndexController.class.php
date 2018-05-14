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
        $s_time = strtotime(date('Y-m-d 00:00:01'));   //�����ʱ��
        $e_time = strtotime(date('Y-m-d 00:00:01',strtotime('+1day')));//��ǰʱ���+1�� ;
        $order_where['pay_time'] = array(array('egt',$s_time),array('elt',$e_time));
        //������Ϣ
        $data['order']['jr'] = M("order")->where($order_where)->count();
        $data['order']['sum'] = M("order")->where(['order_status'=>array('gt',1)])->count();
        //��Ʒ��Ϣ
        $goods_where['on_time'] = array(array('egt',$s_time),array('elt',$e_time));
        $data['goods']['jr'] = M("goods")->where($goods_where)->count();
        $data['goods']['sum'] = M("goods")->count();
        //��Ա��Ϣ
        $user_where['reg_time'] = array(array('egt',$s_time),array('elt',$e_time));
        $data['user']['jr'] = M("users")->where($user_where)->count();
        $data['user']['sum'] = M("users")->count();
        $this->assign('data',$data);
        $this->display();
    }
}
