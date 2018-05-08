<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/9
 * Time: 15:28
 *
 */
namespace Admin\Controller;
use Admin\Controller;
/**
 * 角色管理
 */
class RoleController extends BaseController
{
    /**角色列表*/
    public $tab ='admin_role';
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
        $list = M($this->tab)->limit($Page->firstRow.','.$Page->listRows)->where($where)->order("role_id")->
        /*join('admin_role ON admin.role_id=admin_role.role_id','INNER')->*/select();
        $index=$Page->firstRow;//显示序号
        $this->assign('index', $index);
        $this->assign('list', $list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->display('');
    }
    /**
     * 添加角色
     */
    public function add()
    {
        //默认显示添加表单
        if (!IS_POST) {
            $where['visible'] = 1;
            $list = M('system_module')->where($where)->order('orderby')->select();
            foreach($list as $k=>$v){
                if($v['level'] == 1){
                    $arr[$v['mod_id']] =$v;
                }
                if($v['level'] == 2){
                    $arr[$v['p_id']]['child'][] = $v;
                }
                unset($list[$k]);
            }
            $this->assign('list',$arr);
            $this->display();
        }
        if (IS_POST) {
            //如果用户提交数据则添加到表
            if(M($this->tab)->where(array('role_name'=>I('post.role_name')))->find()){
                echo '角色名已经存在';
                exit;
            }
            else{
                $_POST['act_list'] = implode($_POST['act_list'],',');
                $model = M($this->tab)->add(I('post.'));
            }
            if ($model) {
                addLog(session('name').'添加角色',session('adminId'));
                echo 1;
            }
            else {
                echo "异常,添加失败";
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
            $where['visible'] = 1;
            $list = M('system_module')->where($where)->order('orderby')->select();
            foreach ($list as $k => $v) {
                if ($v['level'] == 1) {
                    $arr[$v['mod_id']] = $v;
                }
                if ($v['level'] == 2) {
                    $arr[$v['p_id']]['child'][] = $v;
                }
                unset($list[$k]);
            }
            $map[I("get.id_key")] = I("get.id");
            $model = M($this->tab)->where($map)->find();
            $this->assign('data', $model);
            $this->assign('list', $arr);
            $this->display();
        }
        //修改信息
        if (IS_POST) {
            $map[I("post.id_key")] = array('not in', $_POST[I("post.id_key")]);
            $map['role_name'] = I("role_name");
            $name = M($this->tab)->where($map)->find();
            if ($name) {
                echo "此角色名已经存在";
                exit;
            }
            $map2[I("post.id_key")] = $_POST[I("post.id_key")];
            $_POST['act_list'] = implode($_POST['act_list'], ',');
            $re = M($this->tab)->where($map2)->save($_POST);
            if ($re) {
                addLog(session('name') . '修改角色', session('adminId'));
                echo 1;
                exit;
            } else {
                echo "异常,修改失败";
            }

        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $map[I("post.id_key")] = I("post.id");
        if(M('admin')->where($map)->find()){
            $this->ajaxReturn('请先删除此角色下的管理员');
            exit;
        }
        $result = M($this->tab)->where($map)->delete();
        if($result){
            addLog(session('name').'删除角色',session('adminId'));
            echo 1;
        }else{
            $this->ajaxReturn('异常,删除失败');
            exit;
        }
    }


}



