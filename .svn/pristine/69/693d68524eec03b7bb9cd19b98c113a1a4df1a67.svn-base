<?php
namespace Admin\Controller;
use Admin\Controller;
/**
 * 网站管理
 */
class SiteController extends BaseController
{
    /**
     * 分类列表
     * @return [type] [description]
     */
    public function index()
    {
        /*if($key === ""){
            $model = M('setting');
        }else{
            $where['key'] = array('like',"%$key%");
            $where['description'] = array('like',"%$key%");
            $where['_logic'] = 'or';
            $model = M('setting')->where($where); 
        }*/
        $model = M('site');
        /*$count  = $model->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Extend\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出*/
        $site = $model->select();
        $this->assign('model',$site[0]);
        $this->assign('index',1);
        $this->display();     
    }


    /**
     * 更新信息
     * @param  [type] $id [分类ID]
     * @return [type]     [description]
     */
    public function update()
    {
        if (IS_POST) {
            $model = D("Site");
            if (!$model->create()) {
                $this->error($model->getError());
            }else{
                //如果id为空则添加  否则则修改
                if(I("post.site_id")==''){
                    $re= $model->add(I("post."));
                }
                else{
                   $re = $model->save(I("post."));
                }
                if ($re) {
                    $this->success("网站信息更新成功", U('site/index'));
                } else {
                    $this->error("网站信息更新失败");
                }
                }
        }
    }


}
