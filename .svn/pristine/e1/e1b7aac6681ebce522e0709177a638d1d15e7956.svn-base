<?php
namespace Admin\Model;
use Think\Model;
class SiteModel extends Model{
    protected $_validate = array(
        array('site_name','require','请填写网站名字！'), //默认情况下用正则进行验证
        array('site_keywords','require','请填写关键词！'), //默认情况下用正则进行验证
        array('site_des','require','请填写网站描述！'), //默认情况下用正则进行验证
        array('site_url','require','请填写网站的地址！'), //默认情况下用正则进行验证
        array('key','','字段名已经存在！',0,'unique',self::MODEL_BOTH), // 在新增的时候验证name字段是否唯一
    );
}