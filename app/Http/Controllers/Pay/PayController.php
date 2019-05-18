<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PayController extends Controller
{
    public $app_id;
    public $gate_way;
    public $notify_url;
    public $return_url;
    public $rsaPrivateKeyFilePath;
    public $aliPubKey;
    public function __construct()
    {
        $this->app_id = env('PAY_APP_ID');
        $this->gate_way = 'https://openapi.alipaydev.com/gateway.do';
        $this->notify_url = env('PAY_NOTIFY_URL');
        $this->return_url = env('PAY_RETURN_URL');
        $this->rsaPrivateKeyFilePath = openssl_pkey_get_private("file://".storage_path('app/keys/private.pem'));    //应用私钥
        $this->aliPubKey = openssl_pkey_get_private("file://".storage_path('app/keys/pay_pub.pem')); //支付宝公钥
    }
    public function pay()
    {
        //请求业务参数
        $content = [
            'subject'=> 'h5支付小测试！！！！',
            'out_trade_no' => $_GET['order_sn'],
            'total_amount' => $_GET['order_amount'],
            'product_code' => 'QUICK_WAP_WAY'
        ];
        //公共参数
        $data = [
            'app_id' => $this->app_id,
            'method' => 'alipay.trade.wap.pay',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s',time()),
            'version' => '1.0',
            'notify_url' => $this->notify_url,
            'return_url' => $this->return_url,
            'biz_content' => json_encode($content),
        ];
        //拼接参数
        ksort($data);
        $a = '';
        foreach($data as $k=>$v){
            $a.=$k.'='.$v.'&';
        }
        $b = rtrim($a,'&');
        //签名
        openssl_sign($b,$sign,$this->rsaPrivateKeyFilePath,OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        $data['sign']=$sign;

        //拼接url
        $a1='?';
        foreach($data as $k1=>$v1){
            $a1.=$k1.'='.urlencode($v1).'&';
        }
        $b1 = rtrim($a1,'&');
        $url = $this->gate_way.$b1;
        header('Location:'.$url);
    }
    //同步
    public function alipayReturn()
    {
        header('Location:http://127.0.0.1:8848/hellow world2/ok.html');
    }
    public function alipayNotify()
    {
        $data = $_POST;
        //写入日志
        $log = "\n>>>>>>>>>>>".date('Y-m-d H:i:s',time())."\n".json_encode($data)."\n";
        file_put_contents("logs/notify.log",$log,FILE_APPEND);
        print_r($data);
//        unset($data['sign']);
//        unset($data['sign_type']);
//        ksort($data);
//        $a = '';
//        foreach($data as $k =>$v){
//            $a.=$v.'='.urldecode($v).'&';
//        }
//        $a1 =rtrim($a,'&');

    }
}
