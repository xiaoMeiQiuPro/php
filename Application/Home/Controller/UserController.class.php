<?php
//                                                              _ooOoo_  
//                                                             o8888888o  
//                                                             88" . "88  
//                                                             (| -_- |)                                
//                                                              O\ = /O                                     鹏
//                                                          ____/`---'\____  
//                                                          . ' \\| |// `.                                  仔
//                                                         / \\||| : |||// \  
//                                                       / _||||| -:- |||||- \                              在
//                                                         | | \\\ - /// | |  
//                                                       | \_| ''\---/'' |_/  |                             此
//                                                        \ .-\__ `-` ___/-. /  
//                                                     ___`. .' /--.--\ `. . __                             拜
//                                                  ."" '< `.___\_<|>_/___.' >'"".  
//                                                 | | : `- \`.;`\ _ /`;.`/ - ` : | |                       上
//                                                   \ \ `-. \_ __\ /__ _/ .-` / /  
//                                           ======`-.____`-.___\_____/___.-`____.-'======                  ！
//                                                              `=---='  
//                                    
//                                           .............................................  
//                                                    佛祖保佑             永无BUG 
//                                            佛曰:  
//                                                    写字楼里写字间，写字间里程序员；  
//                                                    程序人员写程序，又拿程序换酒钱。  
//                                                    酒醒只在网上坐，酒醉还来网下眠；  
//                                                    酒醉酒醒日复日，网上网下年复年。  
//                                                    但愿老死电脑间，不愿鞠躬老板前；  
//                                                    奔驰宝马贵者趣，公交自行程序员。  
//                                                    别人笑我忒疯癫，我笑自己命太贱；  
//                                                    不见满街漂亮妹，哪个归得程序员？ 
namespace Home\Controller;
use Think\Controller;
header('Content-Type: text/html; charset=utf-8'); //网页编码
class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取open_id
    function  get_openid(){
        $code = I("post.code");
        $appid = "wx9f42cbf90f7c0f51";
        $secret = "58bb4162459c69fea05d55ba9360f4ba";
        if (!$code) {
            json(407, '参数错误');
        }
        else{
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
             $data =  send_curl($url,'');
            json(200,'success',$data['openid']);
        }
    }

    function jiekou()
    {
        $this->display("index/test");
    }



    //提交业务合作
    function add_cooperation()
    {
        $post = array(
            'user_id' => I('post.user_id'),
            'phone' => I('post.phone'),
            'name' => I('post.name'),
            'content' => I('post.content'),
            'address' => I('post.address')
        );
        $add = M('cooperation')->add($post);
        if ($add) {
            json(200, '提交成功', '');
        }
        else{
            son(200, '异常', '');
        }
    }

    //登录
    function login()
    {
        $account = I('post.account');
        $nick_name = I('post.name');
        $head_url = I('post.head_pic');
        if(!$account || !$nick_name || !$head_url){
            json(407,'参数异常');
        }
        //如果用户存在 则修改资料
        if($user = M('users')->where(['account'=>$account])->find()){
            $address = M("address")->where(['address_id'=>$user['address_id']])->find();
            $address['user_id'] = $user['user_id'];
            $_POST['last_login'] = time();
            $re = M('users')->where(['user_id'=>$user['user_id']])->save(I('post.'));
        }   //不存在则添加资料
        else{
            $address = array();
            $_POST['reg_time'] = time();
            $_POST['last_login'] = time();
            $re =  M('users')->add(I('post.'));
            $address['user_id'] = $re;
        }
        $data = array(
            'name' => $nick_name,
            'head_pic' => $head_url,
        );
        if($re !== false){
            json(200,'登陆成功',$address);
        }
        else{
            json(201,'数据异常,登陆失败');
        }
    }

    //首页
    function index()
    {
        //轮播图
        $re['ad'] = M('ad')->where(array('enabled' => '1'))->field('ad_code,ad_link')->select();
        if(!empty($re['ad'])){
            foreach($re['ad'] as $k=>$v){
                $re['ad'][$k]['ad_code'] = get_ip().$v['ad_code'];
            }
        }
        //商品
        /*$where['start_time'] = array('elt',get_date());
        $where['end_time'] = array('egt',get_date());*/
        $where['is_on_sale'] = 1;
        $re['goods'] = M('goods')->where($where)->order("is_recommend desc")->field('goods_id,original_img,goods_name')->select();
        if(!empty($re['goods'])){
            foreach($re['goods'] as $k=>$v){
                $re['goods'][$k]['original_img'] = get_ip().$v['original_img'];
            }
        }
        json(200, '返回成功', $re);
    }

    //商品详情
    function goods_info()
    {
        $id = I('post.goods_id');
        $user_id = I('post.user_id');
       // $id = 183 ;
        if (!$id||!$user_id) {
            json(407, '参数异常', '');
        }

        $map['goods_id'] = $id;
        $map['is_on_sale'] = 1;
        $goods = M('goods')->where(array('goods_id' => $id))->field('goods_id,goods_sn,goods_name,shop_price,freight,store_count,goods_img,info_img')->find();
        $goods['cart_num'] = M("cart")->where(['user_id'=>$user_id])->count();
        if(!empty($goods)){
                $goods['goods_img'] = explode(',',$goods['goods_img']);
                $goods['info_img'] = explode(',',$goods['info_img']);
                foreach($goods['goods_img'] as $k=>$v){
                    $goods['goods_img'][$k] = get_ip().$v;
                }
                foreach($goods['info_img'] as $k=>$v){
                $goods['info_img'][$k] = get_ip().$v;
            }
            json(200, 'success', $goods);
        }
    }
    //加入购物车
    function add_cart()
    {
        $user_id = $_POST['user_id'];
        $goods_id = $_POST['goods_id'];
        $goods_num = $_POST['number'];
        if (!$user_id || !$goods_id||!$goods_num) {
            json(407, '参数错误');
        }
        $goods_id = $_POST['goods_id'];
        //如果购物车有这个商品,则添加数量
        $sell_cart = M('cart')->where(array('goods_id' => $goods_id, 'user_id' => $_POST['user_id']))->find();
        if ($sell_cart) {
            M('cart')->where(array('goods_id' => $goods_id, 'user_id' => $_POST['user_id']))->setInc('goods_num', $goods_num);
            json(200, '加入购物车成功', '');
        }

       // $goods = M('goods')->where(array('goods_id' => $goods_id))->find();
        $cart = M('cart')->add(array(
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'goods_num' =>$goods_num,
            'add_time' => time(),
        ));
        if ($cart) {
            json(200, '加入购物车成功', '');
        }
        else{
            json(201,'服务器异常,添加失败');
        }
    }

   //地址列表
    function address(){
        $user_id = I("post.user_id");
        if (!$user_id) {
            json(407, '参数错误');
        }
        $data = M("address")->where(['user'=>$user_id])->select();
        json(200,'success',$data);
    }
    //操作收货地址  增 / 删 / 改
    function operation_address(){

        $user_id = I("post.user_id");
        $type  = I('post.type');
        if(!$user_id||!$type){
            json(407, '参数异常');
        }
        if($type != 1 ){
            $address_id = I('post.address_id');
            if(!$address_id){
                json(407, '参数错误');
            }
        }
        //添加
        if($type == 1){
            $data = M("address")->add(I('post.'));
        }
        // 删
        elseif($type == 2 ){
            $data = M("address")->where(['address_id'=>$address_id])->delete();
        }//改
        elseif($type == 3){
            $data = M("address")->where(['address_id'=>$address_id])->save(I('post.'));
            if($data !== false){
                json(200,'success');
            }
            else{ json(201,'操作失败,服务器异常'); }
        }
        if($data){
            json(200,'success');
        }
        else{ json(201,'操作失败,服务器异常'); }
    }

    //商品直接下单
    function add_order()
    {
        $goods_id  = I("post.goods_id");
        $user_id  = I("post.user_id");
        $goods_num = I('post.goods_num');
        $address_id = I('post.address_id');
        /*$goods_id = 183;
        $user_id = 27;
        $goods_num = 1;
        $address_id = 861;*/
        if (!$goods_id || !$user_id || !$goods_num || !$address_id) {
            json(407, '参数错误', '');
        }
        //查询库存
        $res = M('goods')->where(array( 'goods_id' => $goods_id))->find();
        if ($res['store_count'] <= 0 || $_POST['goods_num'] > $res['store_count']) {
            json(203, '库存不足', '');
        }
        //商品价格  //运费  //市场价
            $goods_price = $res['shop_price'];
            $freight = $res['freight'];
        //最终支付价格
        $price = $goods_price * $goods_num + $freight;
        $sn = time().mt_rand(100000,999999);
        //订单数据
        //查询地址数据
        $address = M('address')->where(['address_id'=>$address_id])->find();
        //修改用户默认地址
        M('users')->where(['user_id'=>$user_id])->save(['address_id'=>$address_id]);
        $arr = array(
            'order_sn' => $sn,
            'user_id' => $user_id,
            'consignee' => $address['consignee'], //收货人
            'province' => $address['province'], //省份
            'city' => $address['city'],   //成熟
            'district' => $address['district'],  // 县区
            'address' => $address['address'],   //详情地址
            'mobile' => $address['mobile'],    //手机号
            'shipping_price' => $freight,    //运费
            'order_amount' => $price,       //订单价格
            'add_time' => time(),
        );
        //开启事务   添加订单  和  订单商品
        $order = M('order');
        $order->startTrans();
        // 进行相关的业务逻辑操作
           $re1 = $order->add($arr); // 保存订单信息
         //订单商品信息
           $arr2 = array(
               'order_id' => $re1,
               'goods_id' => $goods_id,
               'goods_sn' => $res['goods_sn'],
               'goods_name' => $res['goods_name'],
               'goods_num' => $goods_num,
               'market_price' => $res['market_price'],
               'goods_price' => $goods_price,
               'freight' => $freight,
               'goods_img' => $res['original_img']
           );
           $re2 = M('order_goods')->add($arr2);
        if ($re2 && $re1){
            // 提交事务
            $order->commit();
            //下单数据
            $this->wx_pay(array(
                'order_sn' => $sn,
                'order_amount' => $price
            ));
        }else{

            $order->rollback();
            json(201,'数据异常,下单失败');
        }
    }
    //购物车购买
    function cart_pay()
    {
        $cart_id = I('post.cart_id');
        $user_id = I('post.user_id');
        /*$cart_id = "4047,4048";
        $user_id = 26;*/
        if(!$cart_id || !$user_id || !I('post.address_id')){
            json(407,'参数错误');
        }
        $map['id']  = array('in',$cart_id);
        $re =  M('cart')->where($map)->alias("a")->join("__GOODS__ b ON a.goods_id = b.goods_id","LEFT")->
        field("a.goods_num,b.goods_id,b.original_img,b.shop_price,b.freight,b.goods_name,b.goods_sn,b.market_price,b.is_on_sale")->
        select();
        //判断商品是否下架 , 是否库存充足
        foreach($re as $k=>$v){
            $goods = M('goods')->where(array( 'goods_id' => $v['goods_id']))->find();
            if ( $v['goods_num'] > $goods['store_count']) {
                json(203, '订单中有商品库存不足', '');
            }
            if(!$goods['is_on_sale']){
                json(204, '订单中有商品已下架', '');
            }
        }
        //dd($re);
        //获取商品价格,添加订单
        $freight = 0 ;  //运费
        $goods_price = 0;  //商品价格
        foreach($re as $k=>$v){
            $freight += $v['freight'];
            $goods_price += $v['shop_price']*$v['goods_num'];
        }
        $price = $freight + $goods_price;
        $sn = time().mt_rand(100000,999999);
        //查询地址数据
        $address = M('address')->where(['address_id'=>I("post.address_id")])->find();
        //修改用户默认地址
        M('users')->where(['user_id'=>$user_id])->save(['address_id'=>I("post.address_id")]);
        $arr = array(
            'order_sn' => $sn,
            'user_id' => $user_id,
            'consignee' => $address['consignee'], //收货人
            'province' => $address['province'], //省份
            'city' => $address['city'],   //成熟
            'district' => $address['district'],  // 县区
            'address' => $address['address'],   //详情地址
            'mobile' => $address['mobile'],    //手机号
            'shipping_price' => $freight,    //运费
            'order_amount' => $price,       //订单价格
            'add_time' => time(),
        );
        //开启事务   添加订单  和  订单商品
        $order = M('order');
        $order->startTrans();
        // 进行相关的业务逻辑操作
        // 保存订单信息
        $re1 = $order->add($arr);
        //添加订单商品信息
        foreach($re as $k=>$v){
            $arr2 = array(
                'order_id' => $re1,
                'goods_id' => $v['goods_id'],
                'goods_sn' => $v['goods_sn'],
                'goods_name' => $v['goods_name'],
                'goods_num' => $v['goods_num'],
                'market_price' => $v['market_price'],
                'goods_price' => $v['shop_price'],
                'freight' => $v['freight'],
                'goods_img' => $v['original_img']
            );
            $re2 = M('order_goods')->add($arr2);
            if(!$re2){
                $order->rollback();
                json(201,'数据异常,下单失败');
            }
        }
        //删除购物车信息
        $re3 =  M('cart')->where($map)->delete();
        if ($re3 && $re1){
            // 提交事务
            $order->commit();
            //下单数据
            $this->wx_pay(array(
                'order_sn' => $sn,
                'order_amount' => $price
            ));
        }else{
            $order->rollback();
            json(201,'数据异常,下单失败');
        }
    }

    //代付款  付款
    function order_pay()
    {
        $_POST['order_id'] = 14;
        if (I('post.order_id')) {
            $re =  M('order_goods')->where(['order_id'=>I('post.order_id')])->/*alias("a")->join("__GOODS__ b ON a.goods_id = b.goods_id","LEFT")->
            field("a.goods_num,b.goods_id,b.original_img,b.shop_price,b.freight,b.goods_name,b.goods_sn,b.market_price,b.is_on_sale")->
            */select();
            //判断商品是否下架 , 是否库存充足
            foreach($re as $k=>$v){
                $goods = M('goods')->where(array( 'goods_id' => $v['goods_id']))->find();
                if ( $v['goods_num'] > $goods['store_count']) {
                    json(203, '订单有商品库存不足', '');
                }
                if(!$goods['is_on_sale']){
                    json(204, '订单有商品已下架', '');
                }
            }
            //
            $this->wx_pay(M('order')->where(['order_id'=>I('post.order_id')])->find());
        } else {
            json(201, '订单不存在', '');
        }
    }










    //购物车
    function cart()
    {
        $user_id = I('user_id');
        if(!$user_id){
            json('407','参数错误');
        }
        $goods = M("cart")->where(['user_id'=>$user_id])->
                 alias("a")->join("__GOODS__ b ON a.goods_id = b.goods_id","LEFT")->
                 field("a.id,a.goods_num,b.goods_id,b.original_img,b.shop_price,b.freight,b.goods_name")->
                 select();
        if(!empty($goods)){
            foreach($goods as $k=>$v){
                $goods[$k]['original_img'] = get_ip().$v['original_img'];
            }
        }


        json(200, '返回购物车', $goods);
    }

//删除购物车
    function del_cart()
    {
        $user_id = I('user_id');
        if(!$user_id){
            json('407','参数错误');
        }
        $map['user_id'] = $user_id;
        if(I("post.id")){
            $map['id'] = array('in', I("post.id"));
        }

        if(M('cart')->where($map)->delete()){
        json(200, '删除成功');
         }
        else{
            json(201, '服务器异常');
        }
    }
    /**
     * 发送下单请求；
     * @param  Curl   $curl 请求资源句柄
     * @return mixed       请求返回数据
     */
    public function wx_pay($order) {
        if(!$order){
            json(202,'订单异常');
        }
      //  dd($order);
      //  $order = M("order")->where(['order_id'=>$order_id])->find();
        $nonce_str = $this->rand_code();        //调用随机字符串生成方法获取随机字符串
        $data['appid'] ='wx9f42cbf90f7c0f51';   //appid
        $data['mch_id'] = '1503869201' ;        //商户号
        $data['body'] = "Mr 治的小商店-购物";
        $data['spbill_create_ip'] = $_SERVER['HTTP_HOST'];   //ip地址
        $data['total_fee'] = 1;                         //金额
        $data['out_trade_no'] = $order['order_sn'];    //商户订单号,不能重复
        $data['nonce_str'] = $nonce_str;                   //随机字符串
        $data['notify_url'] = 'https://www.99youke.com/';   //回调地址,用户接收支付后的通知,必须为能直接访问的网址,不能跟参数
        $data['trade_type'] = 'JSAPI';      //支付方式
        //将参与签名的数据保存到数组  注意：以上几个参数是追加到$data中的，$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
        $data['sign'] = $this->getSign($data);        //获取签名
       // dd($data);
        $xml = $this->ToXml($data);            //数组转xml
        //curl 传递给微信方
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //header("Content-type:text/xml");
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }    else    {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        }
        //设置header
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //传输文件
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            $re = $this->FromXml($data);
            print($re);
            if($re['return_code'] != 'SUCCESS'){
                json(205,'签名失败');
            }
            else{
                $arr =array(
                    'prepayid' =>$re['prepay_id'],
                    'appid' => 'wx9f42cbf90f7c0f51',
                    'partnerid' => '1503869201',
                    'package' => 'Sign=WXPay',
                    'noncestr' => $nonce_str,
                    'timestamp' =>time(),
                );
                $sign = $this->getSign($arr);
                $arr['sign'] = $sign;
                json(200,'签名成功',$arr);
            }
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            json(206,"curl出错，错误码:$error");
        }
    }
    private function getSign($params) {
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key.'='.$item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA."&key="."123457abd!";        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $sign;
    }
    public function ToXml($data=array())
    {
        if(!is_array($data) || count($data) <= 0)
        {
            return '数组异常';
        }

        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    //生成随机字符串
    function rand_code(){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $str = str_shuffle($str);
        $str = substr($str,0,32);
        return  $str;
    }
    public function FromXml($xml)
    {
        if(!$xml){
            echo "xml数据异常！";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }



    //微信回调
    public function weipayverify(){
        //写支付记录，WEB_PATH是我网站的根目录
        $postStr = $this->postdata();//接收post数据
        //把接受的参数转为数组
        $arr = $this->FromXml($postStr);
        //查看微信返回的数据 ,查看一次可删除  ,数据可拿来做测试
        $a =  fopen("./log.txt","a+" );
        fwrite ( $a ,  print_r($arr,true)."\n".get_date()."\r\n");
        //验证支付
        if($arr['return_code'] == "SUCCESS"){
            //验证签名
            $sign = $this->getSign($arr);
            if($sign == $arr['sign']){//验证成功
                //查询订单
                $order = M("order")->where(['order_sn'=>$arr['out_trade_no']])->find();
                $order_goods = M("order_goods")->where(['order_sn'=>$arr['out_trade_no']])->select();
                //修改订单状态
                $order_save = M("order")->where(['order_sn'=>$arr['out_trade_no']])->save(['order_status'=>2,'pay_time'=>time()]);
                if($order_save !== false){
                    //增加用户消费积分 1元等于100分
                    $user_save = M("user")->where(['account'=>$arr['openid']])->setInc('pay_points',$arr['total_fee']);
                    if(!$user_save){
                        fwrite ( $a ,  "用户积分修改失败\n".get_date()."\r\n");
                    }
                    //减少商品库存
                    foreach($order_goods as $k=>$v){
                        $goods_save = M('goods')->where(['goods_id'=>$v['goods_id']])->setDec('store_count',$v['goods_num']);
                        $goods_save2 = M('goods')->where(['goods_id'=>$v['goods_id']])->setInc('sales_sum',$v['goods_num']);
                        if(!$goods_save){
                            fwrite ( $a ,  "商品库存修改失败\n".get_date()."\r\n");
                        }
                        if(!$goods_save2){
                            fwrite ( $a ,  "商品销修改失败\n".get_date()."\r\n");
                        }
                    }
                    fclose($a);
                    return  $this->FromXml(array('return_code'=>'SUCCESS','return_msg'=>"OK"));
                }
                else{
                    fwrite ( $a ,  "订单修改失败\n".get_date()."\r\n");
                }








             }
            else{
                fwrite ( $a ,  "签名验证失败,支付失败\n".get_date()."\r\n");
                fclose ( $a );
            }
        }
        else{
            fwrite ( $a ,  $arr['return_msg']."\n".get_date()."\r\n");
            fclose ( $a );
        }






    }




    // 接收post数据
    /*
    *  微信是用$GLOBALS['HTTP_RAW_POST_DATA'];这个函数接收post数据的
    */
    function postdata(){
        $receipt = $_REQUEST;
        if($receipt==null){
            $receipt = file_get_contents("php://input");
            if($receipt == null){
                $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
            }
        }
        return $receipt;
    }



    //我的订单  未下单
    function my_order()
    {
        $user_id = I('post.user_id');
        /*$_POST['page'] = $_POST['page'] ? $_POST['page'] : 0;
        $_POST['number'] = $_POST['number'] ? $_POST['number'] : 15;*/
        if (!$user_id) {
            json(407, '数据异常', '');
        }
        $where['user_id'] = $user_id;
        $where['order_status'] = 1;
        $order = M("order")->where($where)->
        alias("a")->
        field("order_id,order_amount,shipping_price")->
        select();
        foreach($order as $k=>$v){
            $order[$k]['goods'] = M("order_goods")->where(['order_id'=>$v['order_id']])->
            alias("a")->join("__GOODS__ b ON a.goods_id = b.goods_id","LEFT")->
            field("a.goods_name,b.original_img,a.goods_num,a.goods_price")->
            select();
            foreach($order[$k]['goods'] as $k2=>$v2){
                $order[$k]['goods'][$k2]['original_img'] = get_ip().$v2['original_img'];
            }
        }
        json(200, 'success', $order);
    }

    //取消订单
    function dell_order()
    {
        if (M('order')->where(array('order_id' => $_POST['order_id']))->delete()) {
            M('order_goods')->where(array('order_id' => $_POST['order_id']))->delete();
            json(200, '取消成功', '');
        }
        json(201, '取消失败', '');
    }

    //评价
    function add_comment()
    {
        if (!$_POST['user_id'] || !$_POST['content'] || !$_POST['goods_id'] || !$_POST['star'] || !$_POST['order_id']) {
            json('409');
        } else {
            if (!empty($_FILES['img'])) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 4145728;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = './Uploads/'; // 设置附件上传根目录
                $upload->savePath = '/comment/'; // 设置附件上传（子）目录
                // 上传文件
                $info = $upload->upload();
                if (!$info) {// 上传错误提示错误信息
                    json(405, $upload->getError(), '');
                    // $this->error($upload->getError());
                } else {// 上传成功
                    // $this->success('上传成功！');
                    foreach ($info as $file) {
                        $_POST['img'] .= __ROOT__ . '/Uploads' . $file['savepath'] . $file['savename'] . ",";//头像路径
                    }
                }
            }
            $_POST['img'] = substr($_POST['img'], 0, -1);
        }
        $re = M('comment')->add($_POST);
        if ($re) {
            $up_order = M('order')->where(array('order_id' => $_POST['order_id']))->save(array('pingjia' => '1'));
            json(200, '评价成功', '');
        } else {
            json(202, '服务器异常', '');
        }
    }


    //我的界面
    function my()
    {
        $id = $_POST['user_id'];
        if (empty($id)) {
            json(200, '用户不存在', '');
        }
        $user = M('users')->where(array('user_id' => $id))->field('pay_points,sex,head_pic,level,name,mobile,birthday,city')->find();
        $user['head_pic'] = 'http://' . $_SERVER['HTTP_HOST'] . $user['head_pic'];
        $goods_collect = M('goods_collect')->where(array('user_id' => $id))->select();
        foreach ($goods_collect as $key => $value) {
            # code...
            // $collect = $key + 1;
        }
        if ($goods_collect) {
            $user['goods_collect'] = $key + 1;
        } else {
            $user['goods_collect'] = 0;
        }
        $follow_store = M('follow_store')->where(array('user_id' => $id))->select();
        foreach ($follow_store as $key => $value) {
            # code...
        }
        if ($follow_store) {
            $user['follow_store'] = $key + 1;
                $user['follow_store'] = 0;
        }


        $coupon_list = M('coupon_list')->where(array('uid' => $id))->select();
        foreach ($coupon_list as $key => $value) {
            # code...
        }
        if ($coupon_list) {
            $user['coupon_list'] = $key + 1;

        } else {
            $user['coupon_list'] = 0;
        }

        $user['children'] = M('children')->where(array('user_id' => $id))->select();

        json(200, '返回成功', $user);

    }



    //返回商品全部评价
    function all_comment()
    {
        if (empty($_POST['goods_id'])) {
            json(201, '商品不存在', '');
        }
        $id = $_POST['goods_id'];
        if ($_POST['type'] == 1) {
            $where = array(
                'goods_id' => $id,
                'ping' => '1'
            );
        } else if ($_POST['type'] == 2) {
            $where = array(
                'goods_id' => $id,
                'ping' => '2'
            );
        } else if ($_POST['type'] == 3) {
            $where = array(
                'goods_id' => $id,
                'ping' => '3'
            );
        } else if ($_POST['type'] == 4) {
            $where = array(
                'goods_id' => $id,
                'is_img' => '1'
            );
        }
        // var_dump($where);die;
        $comment = M('comment')->where($where)->field('order_goods_id,content,user_id')->select();
        $hao = M('comment')->where(array('goods_id' => $id, 'ping' => '1'))->field('order_goods_id,content,user_id,is_img,img')->select();
        foreach ($hao as $k1 => $v1) {
            # code...
        }
        $zhong = M('comment')->where(array('goods_id' => $id, 'ping' => '2'))->field('order_goods_id,content,user_id,is_img,img')->select();
        foreach ($zhong as $k2 => $v2) {
            # code...
        }
        $cha = M('comment')->where(array('goods_id' => $id, 'ping' => '3'))->field('order_goods_id,content,user_id,is_img,img')->select();
        foreach ($cha as $k3 => $v3) {
            # code...
        }
        $tu = M('comment')->where(array('goods_id' => $id, 'is_img' => '1'))->field('order_goods_id,content,user_id,is_img,img')->select();
        foreach ($tu as $k4 => $v4) {
            # code...
        }
        $quan = M('comment')->where(array('goods_id' => $id))->field('order_goods_id,content,user_id,is_img,img')->select();
        foreach ($quan as $k5 => $v5) {
            # code...
        }
        // var_dump($comment);die;
        foreach ($comment as $key => $value) {
            # code...
            $order_goods = M('order_goods')->where(array('rec_id' => $value['order_goods_id']))->field('spec_key_name')->find();
            $value['spec_key_name'] = $order_goods['spec_key_name'];
            $user = M('users')->where(array('user_id' => $value['user_id']))->field('head_pic,name')->find();
            $value['touxiang'] = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . $user['head_pic'];
            $value['user_name'] = $user['name'];

            if ($value['is_img'] == 1) {
                $img = explode(',', $value['img']);
                // var_dump($img);
                foreach ($img as $k => $v) {
                    # code...
                    $imag[] = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . $v;
                }
                $value['image'] = $imag;
            } else {
                $value['image'] = array();

            }

            $user_info[] = $value;
        }
        if (empty($user_info)) {
            $user_info = array();
        }
        if ($hao) {
            $shu = $k1 + 1;
        } else {
            $shu = 0;
        }
        $aa['haha'] = $user_info;
        $aa['hao'] = $shu;

        if ($zhong) {
            $shu = $k2 + 1;
        } else {
            $shu = 0;
        }
        $aa['zhong'] = $shu;

        if ($cha) {
            $shu = $k3 + 1;
        } else {
            $shu = 0;
        }
        $aa['cha'] = $shu;

        if ($tu) {
            $shu = $k4 + 1;
        } else {
            $shu = 0;
        }
        $aa['tu'] = $shu;

        if ($quan) {
            $shu = $k5 + 1;
        } else {
            $shu = 0;
        }
        $aa['quan'] = $shu;
        json(200, '返回成功', $aa);
    }

    //系统设置 意见反馈
    function add_feedback()
    {
        for ($i = 1; $i < 10; $i++) {
            if (!empty($_FILES['image' . $i])) {
                $_FILES['image'][] = $_FILES['image' . $i];
            } else {
                break;
            }
            // $i++;
        }
        // echo json_encode($_FILES);die;
        foreach ($_FILES['image'] as $k => $v) {
            # code...

            if (!empty($_FILES['image' . $k]['tmp_name'])) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 3145728;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = './public/upload/'; // 设置附件上传根目录
                $upload->savePath = '/feedback/'; // 设置附件上传（子）目录
                // 上传文件
                $info = $upload->upload();
                if (!$info) {// 上传错误提示错误信息
                    // $this->json(401,$upload->getError(),'');
                    // $touxiang = '';die;
                    // die;
                    // $this->error($upload->getError());
                    // $touxiang[] = $upload->getError();//头像路径
                } else {// 上传成功
                    // $this->success('上传成功！');
                    foreach ($info as $file) {
                        // echo json_encode($file);die;
                        $touxiang[] = '/Uploads/feedback' . $file['savepath'] . $file['savename'];//头像路径
                    }
                }
            } else {
                // $this->json(400,'没有上传头像','');
            }
        }
        // echo json_encode($touxiang);die;
        $post = array(
            'user_id' => I('post.user_id'),
            'feedback' => I('post.feedback'),
            'phone' => I('post.phone')
            // 'img_url'=>$touxiang
        );
        $post = array_filter($post);
        $add = M('feedback')->add($post);
        if ($add) {
            if ($touxiang) {
                foreach ($touxiang as $key => $value) {
                    # code...
                    M('feedback_img')->add(array('img_url' => $value, 'feedback_id' => $add));
                }
            }
            // M('user_log')->add(array('user_id'=>I('post.user_id'),'log_info'=>'提交了意见反馈','time'=>time()));
            json(200, '提交反馈成功', '');
        } else {
            json(400, '提交反馈失败', '');
        }
    }
    //修改个人资料
    function up_user()
    {
        // $file=array(array('children_name'=>'wawaname','children_birthday'=>'12345678901','children_sex'=>'1','user_id'=>'7'));
        // echo json_encode($file);die;
        // echo json_encode($_POST);die
        if (!$_POST['user_id']) {
            json(400, '用户不存在', '');
        }
        if ($_FILES) {
            $upload = new \Think\Upload();// 实例化上传类

            $upload->maxSize = 3145728;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath = './public/upload/'; // 设置附件上传根目录
            $upload->savePath = '/goods_comment/'; // 设置附件上传（子）目录
            // 上传文件 
            $info = $upload->upload();
            // echo json_encode($info);die;
            if (!$info) {// 上传错误提示错误信息
                // json(401,$upload->getError(),'');
                // $this->error($upload->getError());
                $img = '';
            } else {// 上传成功
                // $this->success('上传成功！');
                foreach ($info as $file) {
                    // echo json_encode($file);die;
                    $touxiang = $file['savepath'] . $file['savename'];//头像路径
                }
                foreach ($info as $key => $value) {
                    # code...
                    $img = '/public/upload' . $value['savepath'] . $value['savename'];

                }
            }
        }

        $array = array(
            'name' => $_POST['name'],
            'sex' => $_POST['sex'],
            'head_pic' => $img,
            'mobile' => $_POST['mobile'],
            'birthday' => $_POST['birthday'],
            'city' => $_POST['city']
        );
        $arr = array_filter($array);
        $up = M('users')->where(array('user_id' => $_POST['user_id']))->save($arr);

        M('children')->where(array('user_id' => $_POST['user_id']))->delete();
        $children = json_decode($_POST['json'], true);
        foreach ($children as $key => $value) {
            # code...
            M('children')->add($value);
        }
        $user = M('users')->where(array('user_id' => $_POST['user_id']))->field('head_pic')->find();
        $user['head_pic'] = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . $user['head_pic'];
        json(200, '修改成功', $user);
    }
    //搜索
    function search()
    {
        $name = $_POST['name'];
        if ($_POST['type'] == 1) {//商品
            $where['goods_name'] = array('like', "%$name%");
            $pro = M('goods')->where($where)->limit($_POST['page'], $_POST['number'])->field('goods_id,goods_name,shop_price,goods_remark,original_img')->select();
            foreach ($pro as $key => $value) {
                # code...
                if (!empty($value['original_img'])) {
                    $value['original_img'] = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . $value['original_img'];

                }
                $arr[] = $value;
            }
        } else {//店铺
            $where['store_name'] = array('like', "%$name%");
            $pro = M('user_store')->where($where)->limit($_POST['page'], $_POST['number'])->field('id,admin_id,store_name,store_img,follow')->select();
            foreach ($pro as $key => $value) {
                # code...
                if (!empty($value['store_img'])) {
                    $value['store_img'] = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . $value['store_img'];
                }
                $arr[] = $value;
            }
        }


        json(200, '返回成功', $arr);
    }

    //忘记密码
    function up_password()
    {
        if(!$_POST['code'] || !$_POST['mobile'] || !$_POST['password']){
            json(409);
        }
        $re = M("code")->where(["phone"=>$_POST['mobile']])->order("code_id desc")->find();
        $cur_time = time();
        $old_time =   strtotime($re['time']);

        if(($cur_time - $old_time)>900||$_POST['code'] != $re['code']){
            json(407, '验证码错误', '');
        }
        $post = array(
            'mobile' => $_POST['mobile'],
            'password' => md5($_POST['password'])
        );
        $up = M('users')->where(array('mobile' => $_POST['mobile']))->save($post);
        json(200, '修改密码成功', '');
    }
    //公告
    function return_notice()
    {
        $res = M('notice')->select();
        json(200, '返回成功', $res);
    }

    //客户联系方式
    function return_service()
    {
        $res = M('service')->select();
        json(200, '返回成功', $res);
    }

    //我们
    function we()
    {
        $res = M('we')->find();
        json(200, '返回成功', $res);
    }
//商品列表
    function goods_list(){
        if($_POST['cat_id']){
            $where['cat_id'] = $_POST['cat_id'];
        }
        $order = $_POST['order']?$_POST['order']:'goods_id';
        $where['start_time'] = array('elt',get_date());
        $where['end_time'] = array('egt',get_date());
        $re =  M('goods')->where($where)->field('goods_id,original_img,goods_name')->order($order)->select();
       // echo sql();exit;
        if($re){
            json(200,'success',$re);
        }
        else{
            json(200,'暂无数据','');
        }
    }
//修改资料
   function save_user(){


        $_POST['user'] =   json_decode($_POST['user'],true);
        if(!$_POST['user']['user_id']){
            json(409);
        }
       $id = $_POST['user']['user_id'];
       if (!empty($_FILES['img'])) {
           $upload = new \Think\Upload();// 实例化上传类
           $upload->maxSize = 4145728;// 设置附件上传大小
           $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
           $upload->rootPath = './Uploads/'; // 设置附件上传根目录
           $upload->savePath = '/user/'; // 设置附件上传（子）目录
           // 上传文件
           $info = $upload->upload();
           if (!$info) {// 上传错误提示错误信息
               json(405, $upload->getError(), '');
               // $this->error($upload->getError());
           } else {// 上传成功
               // $this->success('上传成功！');
               foreach ($info as $file) {
                   $_POST['user']['head_pic'] .= __ROOT__ . '/Uploads' . $file['savepath'] . $file['savename'];//头像路径
               }
           }
       }

       $re = M('users')->where(['user_id'=>$id])->save($_POST['user']);
       $_POST['children'] = json_decode($_POST['children'],true) ;
       foreach($_POST['children'] as $k=>$v){
           if($v['id'] == 0){
               $_POST['children'][$k]['user_id'] = $id;
               M('children')->add($_POST['children'][$k]);
           }
           else{
               M('children')->where(['id'=>$v['id']])->save($v);
           }
       }
          json(200,'修改成功');
   }

//搜索商品  商品名和店铺名
  function search_goods(){
      if(!$_POST['val']){
          json(409);
      }
      $map['goods_name|store_name'] = array('like',"%".$_POST['val']."%");
      $map['start_time'] = array('elt',get_date());
      $map['end_time'] = array('egt',get_date());
      $re =  M('goods')->where($map)->join("__STORE__ on __GOODS__.store_id = __STORE__.store_id")->field('goods_id,original_img,goods_name')->select();
      if($re){
          json(200,'success',$re);
      }
      else{
          json(200,'无匹配数据');
      }
  }



  function pay_test(){
      $alipay_config=[
          'appid' =>'2017010604888586',//商户密钥
          'rsaPrivateKey' =>'',//私钥
          'alipayrsaPublicKey'=>'',//公钥(自己的程序里面用不到)
       'partner'=>'2088421540577515',//(商家的参数,新版本的好像用不到)
       'input_charset'=>strtolower('utf-8'),//编码
       'notify_url' =>'www.test.com/api/notify.php',//回调地址(支付宝支付成功后回调修改订单状态的地址)
       'payment_type' =>1,//(固定值)
       'seller_id' =>'',//收款商家账号
       'charset'    => 'utf-8',//编码
       'sign_type' => 'RSA2',//签名方式
       'timestamp' =>date("Y-m-d Hi:i:s"),
       'version'   =>"1.0",//固定值
       'url'       => 'https://openapi.alipay.com/gateway.do',//固定值
       'method'    => 'alipay.trade.app.pay',//固定值
     ];
      require_once('./aop/AopClient.php');
      $content = array();
      $content['body'] = 'ceshi';
      $content['subject'] = 'funbutton';//商品的标题/交易标题/订单标题/订单关键字等
      $content['out_trade_no'] = '';//商户网站唯一订单号
      $content['timeout_express'] = '1d';//该笔订单允许的最晚付款时间
      $content['total_amount'] = floatval($price);//订单总金额(必须定义成浮点型)
      $content['seller_id'] = '';//收款人账号
      $content['product_code'] = 'QUICK_MSECURITY_PAY';//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
      $content['store_id'] = 'BJ_001';//商户门店编号
      $con = json_encode($content);//$content是biz_content的值,将之转化成字符串

      //公共参数
      $param = array();
      $Client  = new \AopClient();
      $param['app_id'] = 'appid';//支付宝分配给开发者的应用ID
      $param['method'] = 'method';//接口名称
      $param['charset'] = 'charset';//请求使用的编码格式
      $param['sign_type'] = 'sign_type';//商户生成签名字符串所使用的签名算法类型
      $param['timestamp'] = 'timestamp';//发送请求的时间
      $param['version'] = 'version';//调用的接口版本，固定为：1.0
      $param['notify_url'] = 'notify_url';//支付宝服务器主动通知地址
      $param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式

      //生成签名
      $paramStr = $Client->getSignContent($param);
      $sign = $Client->alonersaSign($paramStr,$alipay_config['rsaPrivateKey'],'RSA2');

      $param['sign'] = $sign;
      $str = $Client->getSignContentUrlencode($param);

      $param['sign'] = $sign;
      $str = $Client->getSignContentUrlencode($param);
  }
    function about(){
        $data = M('config')->where(['name'=>'about'])->find()['value'];
        $this->assign('data',$data);
        $this->display('about/index');
    }
}
