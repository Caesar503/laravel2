<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Goods;
use App\Model\Cart;
class GoodsController extends Controller
{
    public function goodsinfo()
    {
        //获取商品信息
        $res = Goods::get()->toArray();
        echo json_encode($res);
    }
    //添加购物车
    public function addCart(Request $request)
    {
        $id = $request->input('gid');
        $uid = $request->input('uid');
        //查询商品信息
        $info = Goods::where('id',$id)->first();
        if(!$info){
            $a=[
                'num'=>2,
                'msg'=>'没有此商品信息'
            ];
            echo json_encode($a);die;
        }
        $data = [
            'goods_id'=>$info->id,
            'goods_name'=>$info->goods_name,
            'goods_price'=>$info->goods_price,
            'uid'=>$uid,
        ];
        //查询在购物车是否存在
        $res = Cart::where(['goods_id'=>$id,'is_status'=>1])->first();
        if($res){
            $respon = Cart::where('goods_id',$id)->update(['num'=>$res->num+1]);
        }else{
            $respon = Cart::insertGetId($data);
        }
        //
        if($respon){
            $p = [
                'num'=>0,
                'msg'=>'加入购物车成功！！'
            ];
        }else{
            $p = [
                'num'=>2,
                'msg'=>'服务器内部错误！！'
            ];
        }
        echo json_encode($p);
    }
    //商品详情
    public function goodsDetail(Request $request)
    {
        $id = $request->input('gid');
        //查询商品信息
        $info = Goods::where('id',$id)->first();
        if(!$info){
            $a=[
                'num'=>2,
                'msg'=>'没有此商品信息'
            ];
        }else{
            $a = $info->toArray();
        }
        echo json_encode($a,JSON_UNESCAPED_UNICODE);
    }
}
