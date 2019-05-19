<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WxPayController extends Controller
{
    //统一接口
    public $only = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    //回调接口
    public $notify = "http://passport.zjdgz.com/Weixin/pay_notify";
    //测试
    public function pay($id)
    {
        $orderinfo = Order::where('id',$id)->first();
//        dd($orderinfo);
        //随机字符串
        $str = Str::random(16);
        // 商户订单号
        $order = $orderinfo->order_sn;
        //金额
        $fee = $orderinfo->order_amount;


        //组合参数
        $data = [
            'appid' =>  env('WEIXIN_APPID_0'),  //微信支付绑定的服务号
            'mch_id' =>  env('WEIXIN_MCH_ID'),   //商户的id
//            'device_info' =>''                   //设备终端号（没有-》不写）
            'nonce_str' => $str,                  //随机字符串
            'sign_type' => 'MD5',                //签名类型
            'body' => '开发测试-'.mt_rand(111,999).Str::random(6),
            'out_trade_no' => $order,
            'total_fee' => 1,
            'spbill_create_ip' =>  $_SERVER['REMOTE_ADDR'],
            'notify_url' => $this->notify,
            'trade_type' => 'NATIVE'
        ];
//        print_r($data);die;

        //签名
        $this->values = [];
        $this->values = $data;
        $this->SetSign();
//        print_r($this->values);
        $xml = $this->ToXml(); //转化为xml格式
//        echo $xml;


        //请求接口->接收响应数据
        $res = $this->postXmlCurl($xml,$this->only, $useCert = false, $second = 30);
        $res = simplexml_load_string($res);
        $url = $res->code_url;
        $u = [
            'url'=>$url
        ];
        echo json_encode($u);
//        return view('weixin.test',['url'=>$url,'oid'=>$orderinfo->id]);
    }
    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }
    private function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    //转化为xml格式
    protected function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            die("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
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
    private  function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//		if($useCert == true){
//			//设置证书
//			//使用证书：cert 与 key 分别属于两个.pem文件
//			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//			curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
//			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//			curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
//		}
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            $data = [
                'status'=>'curl出错',
                'code'=>$error
            ];
            die(json_encode($data));
        }
    }
    //查询支付状态
    public function findpay($id){
        $pay = Order::where(['id'=>$id])->first();
        if($pay){
            if($pay->pay_status>0){
                $data = [
                    'status'=>'success',
                    'code'=>200
                ];
            }else{
                $data = [
                    'status'=>'fail',
                    'code'=>45
                ];
            }
        }else{
            $data = [
                'status'=>'订单不存在或者订单已经支付过了',
                'code'=>45
            ];

        }
        return json_encode($data);
    }
    //支付成功
    public function success($id){
        echo '<h3>'.'OrderID:'.$id.'支付成功'.'</h3>';
    }
    public function pay_notify()
    {
        //读取回调
        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);
        $xml = simplexml_load_string($data);
        if($xml->result_code=='SUCCESS' && $xml->return_code=='SUCCESS'){      //微信支付成功回调
            //验证签名
            $sign = true;
            if($sign){       //签名验证成功
                Order::where('order_sn',$xml->out_trade_no)->update(['pay_time'=>time()]);
            }else{
                $arr =  '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];
                file_put_contents('logs/wx_pay_error.log',$arr,FILE_APPEND);
            }
        }
        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;
    }
}
