<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
class UserController extends Controller
{
    //login
    public function login()
    {
        $arr = json_decode(file_get_contents("php://input"),true);
        $res = User::where('email',$arr['email'])->first();
        if($res)
        {
            //判断密码
            if(!password_verify($arr['pass'],$res->pass)){
                $p = [
                    'num'=>2,
                    'error'=>'密码不对哦！'
                ];
            }else
            {
                //获取token
                $token = $this->getToken($res->id);
                $k = 'token_'.$res->id;
                Redis::set($k,$token);
                Redis::expire($k,3600*24*7);

                $p = [
                    'num'=>1,
                    'error'=>'恭喜你登录成功！',
                    'id'=>$res->id,
                    'token'=>$token
                ];
            }
        }else
        {
            $p = [
                'num'=>2,
                'error'=>'邮箱不存在！'
            ];
        }
        echo json_encode($p);
    }
    //获取token
    function getToken($id){
        return substr(Str::random(8).$id.time().$id.rand(111,999),5,15);
    }
}
