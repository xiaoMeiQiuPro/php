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
class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();

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
        $account = I('account');
        $nick_name = I('name');
        $head_url = I('head_pic');
        if(!$account || !$nick_name || !$head_url){
            json(407,'参数异常');
        }
        //如果用户存在 则修改资料
        if($id = M('users')->where(['account'=>$account])->find()){
            $re = M('users')->where(['user_id'=>$id['user_id']])->save(I('post.'));
        }   //不存在则添加资料
        else{
            $re =  M('users')->add(I('post.'));
        }
        if($re){
            json(200,'登陆成功');
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
        $where['is_recommend'] = 1;
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
        $id = I('goods_id');
        $user_id = I('user_id');
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
            json(407, '参数错误');
        }
        if($type !== 1 ){
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

    //下单
    function add_order()
    {
        if (empty($_POST['goods_id'])) {
            json(201, '商品不存在', '');
        }
        if (empty($_POST['user_id'])) {
            json(202, '用户未登录', '');
        }
        $res = M('spec_goods_price')->where(array('key' => $_POST['key'], 'goods_id' => $_POST['goods_id']))->find();
        if ($res['store_count'] <= 0 || $_POST['goods_num'] > $res['store_count']) {
            json(203, '库存不足', '');
        }

        $goods = M('goods')->where(array('goods_id' => $_POST['goods_id']))->find();

        if ($goods['prom_type'] != 0) {
            if ($_POST['goods_num'] > $goods['activity_stock']) {
                json(204, '活动库存不足', '');
            }
            //限时秒杀
            if ($goods['prom_type'] == 6) {
                $seckill = M('goods_seckill')->where(array('id' => $goods['seckill_id']))->find();
                if ($seckill['type'] == 0) {
                    $shu = $seckill['expression'] / 100;
                    $prom_price = $res['price'] * $shu;
                } else if ($seckill['type'] == 1) {
                    $prom_price = $res['price'] - $seckill['expression'];
                } else if ($seckill['type'] == 3) {
                    $prom_price = $res['price'];
                }
                // $goods['prom_id'] = $goods['seckill_id'];
            } else {
                //活动
                $seckill = M('prom_goods')->where(array('id' => $goods['prom_id']))->find();
                if ($seckill['type'] == 0) {
                    $shu = $seckill['expression'] / 100;
                    $prom_price = $res['price'] * $shu;
                } else if ($seckill['type'] == 1) {
                    $prom_price = $res['price'] - $seckill['expression'];
                } else if ($seckill['type'] == 3) {
                    $prom_price = $res['price'];
                }
            }


        } else {
            //普通
            $prom_price = $res['price'];
        }
        //最终支付价格
        $prom_price = $prom_price * $_POST['goods_num'];
        // echo $prom_price;die;
        if (!empty($_POST['coupon_id'])) {
            $list = M('coupon_list')->where(array('id' => $_POST['coupon_id'], 'status' => '0'))->field('cid')->find();
            $cou = M('coupon')->where(array('id' => $list['cid']))->find();
            if ($cou['condition'] <= $prom_price && $cou['use_start_time'] <= time() && $cou['use_end_time'] >= time()) {
                $prom_price = $prom_price - $cou['money'];
                $coupon_id = $_POST['coupon_id'];
                $coupon_price = $cou['money'];
            } else {
                $coupon_id = 0;
                $coupon_price = 0;
            }
        } else {
            $coupon_id = 0;
            $coupon_price = 0;
        }

        $a = substr(str_shuffle(time()), 1) * 99;
        $sn = 'Angel' . $a;
        $arr = array(
            'order_sn' => $sn,
            'user_id' => $_POST['user_id'],
            'consignee' => $_POST['consignee'],
            'country' => $_POST['country'],
            'province' => $_POST['province'],
            'city' => $_POST['city'],
            'district' => $_POST['district'],
            'twon' => $_POST['twon'],
            'address' => $_POST['address'],
            'zipcode' => $_POST['zipcode'],
            'mobile' => $_POST['mobile'],
            'goods_price' => $prom_price,
            'shipping_price' => '0',
            'total_amount' => $prom_price,
            'order_amount' => $prom_price,
            'add_time' => time(),
            'store_id' => $goods['store_id'],
            'cart_order_sn' => time(),
            'order_prom_type' => $goods['prom_type'],
            'coupon_id' => $coupon_id,
            'coupon_price' => $coupon_price
        );
        $order_id = M('order')->add($arr);
        if (!empty($_POST['coupon_id'])) {
            if ($order_id) {
                $up_list_cou = M('coupon_list')->where(array('id' => $_POST['coupon_id']))->save(array(
                    'order_id' => $order_id,
                    'use_time' => time(),
                    'status' => '1'
                ));
            }
        }

        if ($goods['exchange_integral'] != 0) {
            $jifen = $goods['give_integral'];
        } else {
            $jifen = 0;
        }
        $order_goods = M('order_goods')->add(array(
            'order_id' => $order_id,
            'goods_id' => $_POST['goods_id'],
            'goods_name' => $goods['goods_name'],
            'goods_sn' => $goods['goods_sn'],
            'goods_num' => $_POST['goods_num'],
            'market_price' => $goods['market_price'],
            'goods_price' => $goods['shop_price'],
            'cost_price' => $goods['cost_price'],
            'spec_key' => $_POST['key'],
            'spec_key_name' => $res['key_name'],
            'member_goods_price' => $prom_price,
            'prom_type' => $goods['prom_type'],
            'prom_id' => $goods['prom_id'],
            'give_integral' => $jifen
        ));
        $this->pay($order_id);
    }

    //代付款  付款
    function order_pay()
    {
        if ($_POST['order_id']) {
            $this->pay($_POST['order_id']);
        } else {
            json(201, '订单不存在', '');
        }
    }

    //买票  下单
    function piao_add_order()
    {
        if (empty($_POST['goods_id'])) {
            json(201, '商品不存在', '');
        }
        if (empty($_POST['user_id'])) {
            json(202, '用户未登录', '');
        }
        $goods_id = $_POST['goods_id'];
        $goods = M('goods')->where(array('goods_id' => $goods_id))->find();
        if ($goods['adult_stock'] < intval($_POST['adult_number'])) {
            json(203, '成人票库存不够', '');
        }
        if ($goods['children_stock'] < intval($_POST['children_number'])) {
            json(204, '儿童票库存不够', '');
        }

        $children_price = $goods['children_price'] * $_POST['children_number'];//儿童票总价
        $adult_price = $goods['adult_price'] * $_POST['adult_number'];//成人票总价
        $prom_price = $children_price + $adult_price;

        $a = substr(str_shuffle(time()), 1) * 99;
        $sn = 'Angel' . $a;
        $arr = array(
            'order_sn' => $sn,
            'user_id' => $_POST['user_id'],
            // 'consignee'=>$_POST['consignee'],
            // 'country'=>$_POST['country'],
            // 'province'=>$_POST['province'],
            // 'city'=>$_POST['city'],
            // 'district'=>$_POST['district'],
            // 'twon'=>$_POST['twon'],
            // 'address'=>$_POST['address'],
            // 'zipcode'=>$_POST['zipcode'],
            // 'mobile'=>$_POST['mobile'],
            // 'goods_price'=>$prom_price,
            'shipping_price' => '0',
            'total_amount' => $prom_price,
            'order_amount' => $prom_price,
            'add_time' => time(),
            'store_id' => $goods['store_id'],
            'cart_order_sn' => time(),
            'children_price' => $children_price,
            'children_number' => $_POST['children_number'],
            'adult_price' => $adult_price,
            'adult_number' => $_POST['adult_number'],
            'set_address' => $goods['set_address'],
            'set_time' => $goods['set_time'],
            'route' => $goods['route']
            // 'order_prom_type'=>$goods['prom_type'],
            // 'coupon_id'=>$coupon_id,
            // 'coupon_price'=>$coupon_price
        );
        $order_id = M('order')->add($arr);
        if ($order_id) {
            $this->pay($order_id);

        } else {
            json(205, '不晓得咋搞的,下单失败', '');
        }

    }


    //pay支付
    function pay($order_id)
    {
        // echo $order_id;die;
        // $order_id = 1514;
        $order = M('order')->where(array('order_id' => $order_id))->find();
        // echo json_encode($order);die;
        require_once('./pay/aop/AopClient.php');
        $shu = srand((double)microtime() * 1000000);
        $c = new \AopClient;
        $dat = array(
            'app_id' => '2017010304816030',
            'biz_content' => json_encode(array(
                'subject' => '欢迎下次购买',
                'out_trade_no' => $order['order_sn'],
                'total_amount' => $order['order_amount'],
                'product_code' => 'QUICK_MSECURITY_PAY'
            )),
            'charset' => 'utf-8',
            'method' => 'alipay.trade.app.pay',
            'notify_url' => 'https://www.baidu.com',
            'sign_type' => 'RSA',
            'timestamp' => date('Y-m-d H:i:s', time()),
            'version' => '1.0'
        );
        $data = $c->getSignContent($dat);
        // echo $data;die;
        $res = $c->sign($data);
        // echo json_encode($res);die;
        $i = 0;
        $a = '';
        foreach ($dat as $key => $value) {
            # code...
            if ($i == 0) {
                $a .= $key . '=' . urlencode($value);
            } else {
                $a .= '&' . $key . '=' . urlencode($value);
            }

        }
        $data = $data . '&sign=' . urlencode($res);

        json(200, '签名成功', $data);
    }

    //pay回调
    function pay_notify_url()
    {
        // file_put_contents("newfilea.txt",json_encode($_POST));
        require_once('./pay/aop/AopClient.php');

        // require_once('application/libraries/pay/aop/AopClient.php');
        // $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
        // // fwrite($myfile, $_POST);
        // file_put_contents("newfilea.txt",json_encode($_POST));
        $shu = '{"gmt_create":"2017-06-27 16:56:02","charset":"utf-8","seller_email":"liwei_hensatuo@163.com","subject":"\u8d2d\u7269\u8f66","sign":"FxEeFYqkY9dk8jOlXyq0iNqwy27SkRUiZK3IDzVokyskl2rG2vnkAWeVLBYt+3blqTjh+q0ouRs\/73g2KxvhWww6AQyPvYhStfjj+oiWza6su3ITJqGkT7FJu43Y0lFszRAjCsnkeP98VjEkeLuy2KM9rc04A4yp+KcirrhwUsI=","buyer_id":"2088022003197619","invoice_amount":"0.08","notify_id":"6a2ace753502a3210e8ccf144795184kpi","fund_bill_list":"[{\"amount\":\"0.08\",\"fundChannel\":\"ALIPAYACCOUNT\"}]","notify_type":"trade_status_sync","trade_status":"TRADE_SUCCESS","receipt_amount":"0.08","app_id":"2017010304816030","buyer_pay_amount":"0.08","sign_type":"RSA","seller_id":"2088421735959795","gmt_payment":"2017-06-27 16:56:03","notify_time":"2017-06-27 16:56:03","version":"1.0","out_trade_no":"Angel58763475342","total_amount":"0.08","trade_no":"2017062721001004610274477954","auth_app_id":"2017010304816030","buyer_logon_id":"101***@qq.com","point_amount":"0.00"}';
        $_POST = json_decode($shu, true);
        // var_dump($_POST['sign']);die;
        // $_POST =
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMbDuT2JzxTsBDnFgycdKauZZlS3nEqWurKzE2V26joHAEEnqQWqkQCNe/mMJXJszQQoehiJQ3cEOsdP34XpNh2Rg5fLWN436PkoHZbyfcOBk6A3AxGA6bNcDFSpqa7Uxxo6txN3HzsGfEa+Tg1haZy6wjBQYRUrd92QWnidYDgQIDAQAB';
        $public = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMbDuT2JzxTsBDnFgycdKauZZlS3nEqWurKzE2V26joHAEEnqQWqkQCNe/mMJXJszQQoehiJQ3cEOsdP34XpNh2Rg5fLWN436PkoHZbyfcOBk6A3AxGA6bNcDFSpqa7Uxxo6txN3HzsGfEa+Tg1haZy6wjBQYRUrd92QWnidYDgQIDAQAB';
        // var_dump($_POST);die;
        $result = $aop->rsaCheckV1($_POST, $public, "RSA");
        // var_dump($result);die;
        // if($result) {//验证成功
        // echo 1;die;
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //请在这里加上商户的业务逻辑程序代


        //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

        //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

        //商户订单号
        // file_put_contents("newfile.txt",'1');die;

        $out_trade_no = $_POST['out_trade_no'];

        //支付宝交易号

        $trade_no = $_POST['trade_no'];

        //交易状态
        $trade_status = $_POST['trade_status'];


        if ($_POST['trade_status'] == 'TRADE_FINISHED') {

            //判断该笔订单是否在商户网站中已经做过处理
            //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
            //如果有做过处理，不执行商户的业务程序

            //注意：
            //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
        } else if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $_POST['auth_app_id'] == '2017010304816030' && $_POST['subject'] == '购物车') {
            // file_put_contents("newfile.txt",'1');die;

            //判断该笔订单是否在商户网站中已经做过处理
            //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
            //如果有做过处理，不执行商户的业务程序
            //注意：
            //付款完成后，支付宝系统发送该交易状态通知
            $up = M('order')->where(array('cart_order_sn' => $out_trade_no))->save(array('pay_time' => time(), 'pay_status' => '1', 'pay_code' => $trade_no, 'pay_name' => '支付宝'));
            if ($up) {
                $order = M('order')->where(array('cart_order_sn' => $out_trade_no))->select();
                foreach ($order as $key => $value) {
                    # code...
                    $pro = M('order_goods')->where(array('order_id' => $value['order_id']))->select();
                    foreach ($pro as $k => $v) {
                        # code...
                        // $goods_sun = M('goods')->where(array('goods_id'=>$v['goods_id']))->
                        if ($value['order_prom_type'] != '0') {
                            M('goods')->where(array('goods_id' => $v['goods_id']))->setInc('activity_sell', $v['goods_num']);
                            M('goods')->where(array('goods_id' => $v['goods_id']))->setDec('activity_stock', $v['goods_num']);
                            M('goods')->where(array('goods_id' => $v['goods_id']))->setInc('sales_sum', $v['goods_num']);
                            M('goods')->where(array('goods_id' => $v['goods_id']))->setDec('store_count', $v['goods_num']);
                        } else {
                            M('goods')->where(array('goods_id' => $v['goods_id']))->setInc('sales_sum', $v['goods_num']);
                            M('goods')->where(array('goods_id' => $v['goods_id']))->setDec('store_count', $v['goods_num']);
                        }
                        // M('goods')
                    }
                    M('user_store')->where(array('id' => $value['store_id']))->setInc('sale', $value['order_amount']);
                }
                // M('user_store')->where(array('id'=>$order))
            }


            echo "success";
            die;


            //请不要修改或删除
        } else if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $_POST['auth_app_id'] == '2017010304816030' && $_POST['subject'] == '欢迎下次购买') {
            $up = M('order')->where(array('order_sn' => $out_trade_no))->save(array('pay_time' => time(), 'pay_status' => '1', 'pay_code' => $trade_no, 'pay_name' => '支付宝'));

            if ($up) {
                $order = M('order')->where(array('order_sn' => $out_trade_no))->select();
                // foreach ($order as $key => $value) {
                # code...
                $pro = M('order_goods')->where(array('order_id' => $order['order_id']))->select();
                foreach ($pro as $k => $v) {
                    # code...
                    // $goods_sun = M('goods')->where(array('goods_id'=>$v['goods_id']))->
                    if ($order['order_prom_type'] != '0') {
                        M('goods')->where(array('goods_id' => $v['goods_id']))->setInc('activity_sell', $v['goods_num']);
                        M('goods')->where(array('goods_id' => $v['goods_id']))->setDec('activity_stock', $v['goods_num']);
                        M('goods')->where(array('goods_id' => $v['goods_id']))->setInc('sales_sum', $v['goods_num']);
                        M('goods')->where(array('goods_id' => $v['goods_id']))->setDec('store_count', $v['goods_num']);
                    } else {
                        M('goods')->where(array('goods_id' => $v['goods_id']))->setInc('sales_sum', $v['goods_num']);
                        M('goods')->where(array('goods_id' => $v['goods_id']))->setDec('store_count', $v['goods_num']);
                    }
                    // M('goods')
                }
                M('user_store')->where(array('id' => $order['store_id']))->setInc('sale', $order['order_amount']);
                // }
                // M('user_store')->where(array('id'=>$order))
            }


            echo "success";
            die;
        } else if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $_POST['auth_app_id'] == '2017010304816030' && $_POST['subject'] == '报事报修') {


            echo "success";
            die;
        } else if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $_POST['auth_app_id'] == '2017010304816030' && $_POST['subject'] == '余额充值') {

            echo "success";
            die;

        }
        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        // file_put_contents("newfile.txt",'4');die;

        echo "fail";

        // }else {
        //     //验证失败
        //     echo "fail"; //请不要修改或删除

        // }

    }


    //收藏商品
    function collection()
    {
        $post = array(
            'user_id' => $_POST['user_id'],
            'goods_id' => $_POST['goods_id']
        );
        $res = M('goods_collect')->where($post)->find();
        if ($res) {
            M('goods_collect')->where($post)->delete();
            json(200, '取消收藏成功', '');
        } else {
            $post['add_time'] = time();
            M('goods_collect')->add($post);
            json(200, '收藏成功', '');
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
                 field("a.id,a.goods_num,b.goods_id,b.original_img,b.shop_price,b.freight")->
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

    function cart_pay($cart_order, $money)
    {

        require_once('./pay/aop/AopClient.php');
        $shu = srand((double)microtime() * 1000000);
        $c = new \AopClient;
        $dat = array(
            'app_id' => '2017010304816030',
            'biz_content' => json_encode(array(
                'subject' => '欢迎下次购买',
                'out_trade_no' => $cart_order,
                'total_amount' => $money,
                'product_code' => 'QUICK_MSECURITY_PAY'
            )),
            'charset' => 'utf-8',
            'method' => 'alipay.trade.app.pay',
            'notify_url' => 'https://www.baidu.com',
            'sign_type' => 'RSA',
            'timestamp' => '2014-07-24 03:07:50',
            'version' => '1.0'
        );
        $data = $c->getSignContent($dat);
        // echo $data;die;
        $res = $c->sign($data);
        // echo json_encode($res);die;
        $i = 0;
        $a = '';
        foreach ($dat as $key => $value) {
            # code...
            if ($i == 0) {
                $a .= $key . '=' . urlencode($value);
            } else {
                $a .= '&' . $key . '=' . urlencode($value);
            }

        }
        $data = $data . '&sign=' . urlencode($res);
        json(200, '签名成功', $data);
    }


    


    //我的订单  未下单
    function my_order()
    {
        $user_id = I('post.user_id');
      //  $user_id = 26;
        /*$_POST['page'] = $_POST['page'] ? $_POST['page'] : 0;
        $_POST['number'] = $_POST['number'] ? $_POST['number'] : 15;*/
        if (!$user_id) {
            json(201, '用户不存在', '');
        }
        $where['user_id'] = $user_id;
        $where['order_status'] = 1;
        $order = M("order")->where($where)->
        alias("a")->join("__GOODS__ b ON a.goods_id = b.goods_id","LEFT")->
        field("a.order_id,a.shipping_price,a.goods_id,order_amount,a.goods_price,a.goods_num,b.original_img")->
        select();
      //  dd($order);
        foreach($order as $k=>$v){
            $order[$k]['original_img'] = get_ip().$v['original_img'];
        }
        json(200, 'success', $order);
    }

    //取消订单
    function dell_order()
    {
        if (M('order')->where(array('order_id' => $_POST['order_id']))->delete()) {
           /* M('order_goods')->where(array('order_id' => $_POST['order_id']))->delete();*/
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
        } else {
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
       $a =  fopen("./log.txt","a+" );
 fwrite ( $a ,  print_r($_POST,true)."\n".get_date()."\n");
       fwrite($a ,json_decode($_POST['user'],true) );
 fwrite ( $a , $_POST."\n".get_date()."\n");
 fclose ( $a );

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
    //删除孩子信息
    function del_children(){
        if(!$_POST['id']){
            json(409);
        }
       $re = M('children')->where(['id'=>$_POST['id']])->delete();
        if($re) {
            json(200,'删除成功');
        }
        else{
            json(202,'数据异常');
        }
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


    function test(){
        $a = '[
  {
    "children_sex" : "1",
    "id" : "13",
    "children_birthday" : "1503331200",
    "user_id" : "25",
    "children_name" : "孩子"
  },
  {
    "children_sex" : "1",
    "id" : "12",
    "children_birthday" : "1503331200",
    "user_id" : "25",
    "children_name" : "呵呵"
  }
]';      //  $a = '{"name":"\u79cb\u5927\u738b"}' ;
    //    $a = '[{"name":"\U5475\U5475","id":666},{"name":"\U5475\U5475","id":666}]';

     $a =  json_decode($a,true);

        dd($a);

    }
    function test1(){
        $a= array(
            'name' => '秋大王'
        );
        echo json_encode($a);
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
