<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/17
 * Time: 18:50
 */


class ApplyController extends BaseController
{

    public $tab = 'pay_log';

    public function __construct()
    {
        parent::__construct();
    }
    /**列表*/
    public function index($key = "")
    {


        $count = M($this->tab)->alias('a')->
        join("__STORE__ b on a.store_id = b.store_id",'left')->
        join("__ADMIN__ c on a.admin_id = c.admin_id",'left')->
        where('a.status = 0')->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->
        alias('a')->
        join("__STORE__ b on a.store_id = b.store_id",'left')->
        join("__ADMIN__ c on a.admin_id = c.admin_id",'left')->
        where('a.status = 0')->
        limit($Page->firstRow . ',' . $Page->listRows)->
        order("status,id desc")->
        field("a.*,b.store_name,c.admin_name,c.phone")->
        select();
        // dd($list);exit;
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('');
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
    //申请记录
    function my(){
        if(session('adminID') !=1){
            $map['store_id'] = session('store_id');
        }
        $count = M($this->tab)->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M($this->tab)->limit($Page->firstRow . ',' . $Page->listRows)->where($map)->
//        field("project.*,cat.name cat_name,area.name area_name")->
        select();
        // dd($list);exit;
        $index = $Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->display('');
    }
    //申请提现
    function add_pay(){
        $map['store_id'] = session('store_id');
        if(!IS_POST){
            $list = M('store')->where($map)->
//        field("project.*,cat.name cat_name,area.name area_name")->
            find();
            $this->assign('list',$list);
            $this->display();
        }
        else{

            $_POST['pay_price'] = $_POST['pay_price'] - $_POST['price'];
            if($_POST['pay_price']<=0){
                echo "提现额度超出可用额度";
            }
            //开启事务
            M('store')->startTrans();
            $re = M('store')->where($map)->save($_POST); //修改提现申请资料
            $_POST['time'] = get_date();
            $_POST['admin_id'] = session('adminId');
            $_POST['store_id'] = session('store_id');
            $re1 = M('pay_log')->add($_POST);  //提交申请
            if ($re&&$re1){
                // 提交事务
                addLog( '提现申请', session('adminId'));
                M('store')->commit();
                echo 1;
            }else{
                // 事务回滚
                M('store')->rollback();
                echo "数据异常" ;
            }

        }
    }
    //审核提现
    function sh_pay(){
        if(!IS_POST){
            $id= I("get.id");
            $this->assign('id',$id);
            $this->assign('store_id',I("get.store_id"));
            $this->assign('price',I("get.price"));
            $this->display();
        }
        else{
            //如果审核未通过则,返回额度
            if(I('post.status') == 2){
                //开启事务
                M('store')->startTrans();
                $re = M('store')->where(['store_id'=>I('post.store_id')])->setInc('pay_price',I('post.price')); // 用户的积分加3
                $re1  =  M('pay_log')->where(['id'=>I('post.id')])->save($_POST);
                if ($re&&$re1){
                    // 提交事务
                    addLog( '审核成功', session('adminId'));
                    M('store')->commit();
                    echo 1;
                }else{
                    // 事务回滚
                    M('store')->rollback();
                    echo "数据异常" ;
                }
            }
            else{
                $re  =  M('pay_log')->where(['id'=>I('post.id')])->save($_POST);
                if($re){
                    addLog( '提现审核', session('adminId'));
                    echo 1;
                }
                else{
                    echo '数据异常';
                }
            }

        }


    }

}