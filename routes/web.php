<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//测试
Route::any('/Text/test','Test\TextController@test');

Route::get('/info', function () {
    phpinfo();
});

//登录
Route::post('/login','User\UserController@login');
//注册
Route::post('/regist','User\UserController@regist');
//获取商品信息
Route::post('/goodsinfo','Goods\GoodsController@goodsinfo');
//加添购物车
Route::get('/addCart','Goods\GoodsController@addCart');
//商品详情
Route::get('/goodsDetail','Goods\GoodsController@goodsDetail');
//购物车列表
Route::get('/cartList','Cart\CartController@cartList');
//生成订单
Route::get('/createOrder','Cart\CartController@createOrder');
//测试生成订单
Route::get('/test','Cart\CartController@test');
//订单列表
Route::get('/orderList','Cart\CartController@orderList');
//支付宝支付
Route::get('/alipay','Pay\PayController@pay');
//同步
Route::get('/alipay/tong','Pay\PayController@alipayReturn');
//异步
Route::post('/alipay/notify','Pay\PayController@alipayNotify');
