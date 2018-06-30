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

// 登录注册相关路由
Auth::routes();


// 多语言
//Route::get('/lang/{locale}','LanguageController@setLocale')->name('lang');

Route::get('/','StaticPagesController@home') -> name('home');

Route::get('/private-numbers','StaticPagesController@privateNumbers')   -> name('private_numbers');
Route::get('/inactive-numbers','StaticPagesController@inactiveNumbers') -> name('inactive_numbers');
Route::get('/contact','StaticPagesController@contact')                  -> name('contact');
Route::get('/signup', 'UsersController@create')                         ->name('signup');


//此方法是 VerifiesUsers Trait 里的方法，他会自动处理验证逻辑
Route::get('/verification/{token}','Auth\RegisterController@getVerification');
Route::get('/emails/verification_result','StaticPagesController@setresult');
// 号码详情页
Route::get('/detail/{number}', 'PhonecController@detailSms') -> name('phone.detail');
Route::get('/getprice', 'StaticPagesController@getprice')    -> name('getprice');

// 支付链接
Route::view('/payment/recharge','pay/recharge');
Route::post('/payment/recharge','PaymentController@launchPay') -> name('launch');
Route::get('/payment/success','PaymentController@success')     ->name('success');

// 接口 频率 次数：间隔时间
Route::group(['prefix' => 'manager/api'],function () {
    Route::get('keyword','ApiController@setKeyword');
    Route::post('sendmsg','ApiController@sendMsg');
});
Route::group(['prefix' => 'manager/api'], function () {
    Route::get('getPhoneNumber','ApiController@getPhoneNumber') ->name('get.number');
    Route::get('getSmsContent','ApiController@getSmsContent') ->name('get.content');
});

Route::group(['prefix' => 'manager/api/inside'], function () {
    Route::get('getPhoneNumber','ApiController@getPhoneNumber') ->name('get.number');
    Route::get('getSmsContent','ApiController@getSmsContent') ->name('get.content');
});

//paypal支付
Route::any('paypal/ec-checkout', 'PayPalController@getExpressCheckout')->name('ec-checkout');
Route::get('paypal/ec-checkout-success', 'PayPalController@getExpressCheckoutSuccess');
Route::get('paypal/adaptive-pay', 'PayPalController@getAdaptivePay');
Route::post('paypal/notify', 'PayPalController@notify');
Route::get('paypal/success', 'PayPalController@success');

//阿里支付
Route::post('alipay', 'PaysAliWechatController@alipay')->name('alipay');
Route::get('alireturn', 'PaysAliWechatController@alireturn');
Route::post('alinotify', 'PaysAliWechatController@alinotify');

//后台
//Route::group(['prefix' => 'cfcc','namespace' => 'Admin'],function ($router)
//{
//    $router->get('logout', 'LoginController@logout')        -> name('cfcc.logout');
//    $router->get('index', 'IndexController@index')          -> name('cfcc.index');
//    $router->get('home', 'IndexController@home')            -> name('cfcc.home');
//    $router->any('index/edit/{id?}', 'IndexController@edit')-> name('index.edit');
//    $router->get('bal', 'IndexController@bal')              -> name('cfcc.bal');
//    $router->get('show_detail/{email?}', 'IndexController@show_detail') -> name('cfcc.show_detail');
//    $router->get('flush', 'IndexController@bal')            -> name('flush');
//    $router->post('test', 'IndexController@test')           -> name('cfcc.test');
//    $router->post('set_bal', 'IndexController@set_bal')     -> name('cfcc.set_bal');
//    $router->any('set_money', 'IndexController@set_money')  -> name('cfcc.set_money');
//    $router->post('searchContent', 'IndexController@search_content')  -> name('cfcc.searchContent');
//    $router->post('searchUserContent', 'IndexController@searchUserContent')->name('cfcc.searchUserContent') ;
//    $router->any('showContents', 'IndexController@showContents')->name('cfcc.showContents') ;
//
//
//});
//后台登陆、密码修改
Route::any('/me', 'Admin\AdminController@me')->name('me');
Route::any('cfcc/login', 'Admin\LoginController@login')->name('cfcc.login');



//本地测试
// 接口 频率 次数：间隔时间
Route::group(['prefix' =>'ceshi','middleware' => 'throttle:60,1'],function () {
    Route::get('ck','ApiCeshiController@setKeyword');
    Route::get('cp','ApiCeshiController@getPhoneNumber') ->name('get.number');
    Route::get('cn','ApiCeshiController@getSmsContent') ->name('get.content');
});




Route::group(['prefix' => 'cfcc','namespace' => 'Admin'],function ($router)
{
    $router->get('logout', 'LoginController@logout')        -> name('cfcc.logout');
    $router->get('index', 'CeShiController@index')          -> name('cfcc.index');
    $router->get('home', 'CeShiController@home')            -> name('cfcc.home');
    $router->any('index/edit/{id?}', 'CeShiController@edit')-> name('index.edit');
    $router->get('bal', 'CeShiController@bal')              -> name('cfcc.bal');
    $router->get('show_detail/{email?}', 'CeShiController@show_detail') -> name('cfcc.show_detail');
    $router->get('flush', 'CeShiController@bal')            -> name('flush');
    $router->post('test', 'CeShiController@test')           -> name('cfcc.test');
    $router->post('set_bal', 'CeShiController@set_bal')     -> name('cfcc.set_bal');
    $router->any('set_money', 'CeShiController@set_money')  -> name('cfcc.set_money');
    $router->post('searchContent', 'CeShiController@search_content')  -> name('cfcc.searchContent');
    $router->post('searchUserContent', 'CeShiController@searchUserContent')->name('cfcc.searchUserContent') ;
    $router->any('showContents', 'CeShiController@showContents')->name('cfcc.showContents') ;

});