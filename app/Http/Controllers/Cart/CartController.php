<?php

namespace App\Http\Controllers\Cart;

use App\Model\Order;
use App\Model\OrderDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class CartController extends Controller
{
    //购物车列表
    public function cartList(Request $request)
    {
        $uid = $_GET['uid'];
        //查询该用户下没有生成订单的购物车列表
        $cartList = Cart::where(['uid'=>$uid,'is_status'=>1])->get()->toArray();
        echo json_encode($cartList);
    }
    //生成订单
    public function createOrder()
    {
        $uid = $_GET['uid'];
        //查询该用户下没有生成订单的购物车列表
        $cartList = Cart::where(['uid'=>$uid,'is_status'=>1])->get()->toArray();

        /*
         * 将其生成订单
         * */
        //获取总价
        $num= 0;
        foreach($cartList as $k=>$v){
            $num += $v['goods_price']*$v['num'];
        }
        //获取订单号
        $order_no = $this->get_order_no($uid);
        //拼接数据
        $data = [
            'order_amount'=>$num,
            'order_sn'=>$order_no,
            'uid'=>$uid,
            'pay_time'=>time()
        ];


        /*
          * 开启事务
          * */
        DB::beginTransaction();
        try{
            //中间逻辑代码
            /*
             * 生成订单
             * */
            $oid = Order::insertGetId($data);
            /*
             * 将购物车加入订单详情表
             * */
            $cart1 = [];
            foreach($cartList as $k=>$v){
                $cart1[$k]['oid']=$oid;
                $cart1[$k]['goods_id']=$v['goods_id'];
                $cart1[$k]['goods_price']=$v['goods_price'];
                $cart1[$k]['goods_name']=$v['goods_name'];
                $cart1[$k]['uid']=$v['uid'];
            }
            OrderDetail::insert($cart1);
            /*
             *  //清空该用户的购物车
             * */
            Cart::where(['uid'=>$uid,'is_status'=>1])->update(['is_status'=>2]);

            DB::commit();
        }catch (\Exception $e) {
            //接收异常处理并回滚
            DB::rollBack();
            $r =[
                'num'=>2,
                'msg'=>'失败了奥？！？！？！？！'
            ];
            die(json_encode($r,JSON_UNESCAPED_UNICODE));
        }
        $r =[
            'num'=>1,
            'msg'=>'生成订单成功！！！该订单号为'.$order_no
        ];
        echo json_encode($r);
    }
    //测试生成订单
    public function test()
    {
        $uid = 13;
        //查询该用户下没有生成订单的购物车列表
        $cartList = Cart::where(['uid'=>$uid,'is_status'=>1])->get()->toArray();
//        print_r($cartList);die;

        /*
         * 将其生成订单
         * */
        //获取总价
        $num= 0;
        foreach($cartList as $k=>$v){
            $num += $v['goods_price']*$v['num'];
        }
        //获取订单号
        $order_no = $this->get_order_no($uid);
        //拼接数据
        $data = [
            'order_amount'=>$num,
            'order_sn'=>$order_no,
            'uid'=>$uid,
            'pay_time'=>time()
        ];



        /*
         * 开启事务
         * */
        DB::beginTransaction();
        try{
            //中间逻辑代码
                /*
                 * 生成订单
                 * */
                $oid = Order::insertGetId($data);
                /*
                 * 将购物车加入订单详情表
                 * */
                $cart1 = [];
                foreach($cartList as $k=>$v){
                    $cart1[$k]['oid']=$oid;
                    $cart1[$k]['goods_id']=$v['goods_id'];
                    $cart1[$k]['goods_price']=$v['goods_price'];
                    $cart1[$k]['goods_name']=$v['goods_name'];
                    $cart1[$k]['uid']=$v['uid'];
                }
                OrderDetail::insert($cart1);
                /*
                 *  //清空该用户的购物车
                 * */
                Cart::where(['uid'=>$uid,'is_status'=>1])->update(['is_status'=>2]);

            DB::commit();
        }catch (\Exception $e) {
            //接收异常处理并回滚
            DB::rollBack();
            $r =[
                'num'=>2,
                'msg'=>'失败了奥？！？！？！？！'
            ];
            die(json_encode($r,JSON_UNESCAPED_UNICODE));
        }
        $r =[
            'num'=>1,
            'msg'=>'生成订单成功！！！'
        ];
        echo json_encode($r);
//        $oid = Order::insertGetId($data);
//        if($oid){
//            //将商品加入订单详情表
//            $cart1 = [];
//            foreach($cartList as $k=>$v){
//                    $cart1[$k]['oid']=$oid;
//                    $cart1[$k]['goods_id']=$v['goods_id'];
//                    $cart1[$k]['goods_price']=$v['goods_price'];
//                    $cart1[$k]['goods_name']=$v['goods_name'];
//                    $cart1[$k]['uid']=$v['uid'];
//            }
//            OrderDetail::insert($cart1);
//            //清空该用户的购物车
//            Cart::where(['uid'=>$uid,'is_status'=>1])->update(['is_status'=>2]);
//            $r =[
//                'num'=>1,
//                'msg'=>'生成订单成功！！！'
//            ];
//        }else{
//            $r =[
//                'num'=>2,
//                'msg'=>'失败了奥？！？！？！？！'
//            ];
//        }
//        echo json_encode($r);
    }
    //生成订单号
    function get_order_no($id)
    {
        return time().substr(Str::random(5).$id.Str::random(10),2,8);
    }
    //订单列表
    public function orderList()
    {
        $uid = $_GET['uid'];
        $orderInfo = Order::where(['uid'=>$uid,'is_status'=>1])->get()->toArray();
        echo json_encode($orderInfo);
    }
}
