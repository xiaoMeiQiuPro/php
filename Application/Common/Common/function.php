<?php
//打印數據
function dd($data)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
	exit;
}
function get_ip(){

	return "http://".$_SERVER["HTTP_HOST"];
}


//打印sql
function sql($table){
	echo M($table)->getLastSql();
}
//獲取添加的id
function getId($table){
	return M($table)->getLastInsID();
}
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
	$type       =  $type ? 1 : 0;
	static $ip  =   NULL;
	if ($ip !== NULL) return $ip[$type];
	if($adv){
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip     =   $_SERVER['HTTP_CLIENT_IP'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip     =   $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u",ip2long($ip));
	$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}
//記錄前後臺用戶操作記錄  用户ID 操作记录    表名
function  addLog($record = '',$id,$table="admin_log"){
	if($table == "admin_log"){
		M($table)->add(array('admin_id'=>$id,'record'=>$record,'ip'=>get_client_ip()));
	}
	else{
		M($table)->add(array('user_id'=>$id,'record'=>$record,'ip'=>get_client_ip()));	}
}

/**
 * 获取排序后的分类
 * @param  [type]  $data  [description]
 * @param  integer $pid   [description]
 * @param  string  $html  [description]
 * @param  integer $level [description]
 * @return [type]         [description]
 */
function getSortedCategory($data,$pid=0,$html="|---",$level=0)
{
	$temp = array();
	foreach ($data as $k => $v) {
		if($v['pid'] == $pid){

			$str = str_repeat($html, $level);
			$v['html'] = $str;
			$temp[] = $v;

			$temp = array_merge($temp,getSortedCategory($data,$v['id'],'|---',$level+1));
		}

	}
	return $temp;
}

/**
 * 根据key，返回当前行的所有数据
 * @param  string  $key  字段key
 * @return array         当前行的所有数据
 */
function getSettingValueDataByKey($key)
{
	return M('setting')->getByKey($key);
}
// 定义一个函数getIP() 客户端IP，
function getIP(){
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else $ip = "Unknow";

	if(preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $ip))
		return $ip;
	else
		return '';
}
function uploadImg($file1,$url='img'){
	if(!empty($file1)){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     10145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
		$upload->savePath  =     '/'.$url.'/'; // 设置附件上传（子）目录
		// 上传文件
		$info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			//return $upload->getError();
			// $this->error($upload->getError());
		}else{// 上传成功
			// $this->success('上传成功！');
			foreach($info as $file){
				// echo json_encode($file);die;
			return 	$img = __ROOT__.'/Uploads'.$file['savepath'].$file['savename'];//头像路径
			}
		}
	}
}
/**
* 根据key返回field字段
* @param  string $key   [description]
* @param  string $field [description]
* @return string        [description]
*/
function getSettingValueFieldByKey($key,$field)
{
	return M('setting')->getFieldByKey($key,$field);
}

//获取当前时间
function get_date(){
	return date('Y-m-d H:i:s',time());
}

