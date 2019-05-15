<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App;
class TextController extends Controller
{
    public function test(){
    	
    	// var_dump($res1);
    	$k = '全部信息';
    	$res1 = Redis::get($k);
    	$res1 = json_decode($res1);
    	if($res1==''){
    		echo '没有缓存';
    		$res1 = App\Test::all()->toArray();
    		$res1 = json_encode($res1);
    		$res = Redis::set($k,$res1,1);
    		var_dump($res);
    	}
    	var_dump($res1);
    	return view('Test.test');
    }
}
