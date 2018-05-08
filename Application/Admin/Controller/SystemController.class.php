<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/5
 * Time: 18:21
 */
namespace Admin\Controller;
use Admin\Controller;
class SystemController extends BaseController
{
    public $tab = "config";
    function jf(){
        $map['module'] = 'jf';
        if(!$_POST){
            $list = M($this->tab)->where($map)->select();
            //
            $this->assign('list',$list);
            $this->display();
        }
        else{
            $a = '';
            foreach($_POST as $k=>$v){
                $map['name'] = $k ;
                M($this->tab)->where($map)->save(['value'=>$v]);
            }
            addLog(session('name').'修改積分獎勵規則',session('adminId'));
            $this->success('修改成功', U('System/jf'), 2);
        }


    }
    function email_index(){
        $map['module'] = 'email';
        if(!$_POST){
            $list = M($this->tab)->where($map)->select();
            $this->assign('list',$list);
            $this->display();
        }
        else{
            foreach($_POST as $k=>$v){
                $map['name'] = $k ;
                M($this->tab)->where($map)->save(['value'=>$v]);
            }
            addLog(session('name').'修改郵箱配置',session('adminId'));
            $this->success('修改成功', U('System/email_index'), 2);
        }
    }
    function about(){
        $map['name'] = 'about';
        if(I("post.value")){
            M($this->tab)->where($map)->save(['value'=>$_POST['value']]);
            addLog(session('name').'修改关于我们',session('adminId'));
            $this->success('修改成功', U('System/about'), 2);
        }
        if(!$_POST){
            $list = M($this->tab)->where($map)->find()['value'];
            $this->assign('list',$list);
            $this->display();
        }
    }
}