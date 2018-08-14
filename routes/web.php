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

Route::get('/', 'StaticPagesController@home')->name('home');

Route::get('/private-numbers', 'StaticPagesController@privateNumbers')->name('private_numbers');
Route::get('/api', 'StaticPagesController@api')->name('api');
Route::get('/download', 'StaticPagesController@download')->name('download');
Route::get('/inactive-numbers', 'StaticPagesController@inactiveNumbers')->name('inactive_numbers');
Route::get('/contact', 'StaticPagesController@contact')->name('contact');
Route::get('/signup', 'UsersController@create')->name('signup');


//此方法是 VerifiesUsers Trait 里的方法，他会自动处理验证逻辑
Route::get('/verification/{token}', 'Auth\RegisterController@getVerification');
Route::get('/emails/verification_result', 'StaticPagesController@setresult');
// 号码详情页
Route::get('/detail/{number}', 'PhonecController@detailSms')->name('phone.detail');
Route::get('/getprice', 'StaticPagesController@getprice')->name('getprice');

// 支付链接
Route::view('/payment/recharge', 'pay/recharge');
Route::post('/payment/recharge', 'PaymentController@launchPay')->name('launch');
Route::get('/payment/success', 'PaymentController@success')->name('success');

// 接口 频率 次数：间隔时间
Route::group(['prefix' => 'manager/api'], function () {
    Route::get('keyword', 'ApiController@setKeyword');
    Route::post('sendmsg', 'ApiController@sendMsg');
});
Route::group(['prefix' => 'manager/api'], function () {
    Route::get('getPhoneNumber', 'ApiController@getPhoneNumber')->name('get.number');
    Route::get('getSmsContent', 'ApiController@getSmsContent')->name('get.content');
});

Route::group(['prefix' => 'manager/api/inside'], function () {
    Route::get('key', 'ApiSmsController@key');
    Route::get('phone', 'ApiSmsController@phone');
});
Route::group(['prefix' => 'manager/api/inside'], function () {
    Route::get('getPhoneNumber', 'ApiController@getPhoneNumber')->name('get.number');
    Route::get('getSmsContent', 'ApiController@getSmsContent')->name('get.content');
});

Route::any('manager/api/inside/content', 'ApiSmsController@content');


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


//后台登陆、密码修改
Route::any('/me', 'Admin\AdminController@me')->name('me');
Route::any('cfcc/login', 'Admin\LoginController@login')->name('cfcc.login');


//本地测试
// 接口 频率 次数：间隔时间
Route::group(['prefix' => 'ceshi', 'middleware' => 'throttle:100,1'], function () {
    Route::get('ck', 'SmsOnlineController@setKeyword');
    Route::get('cp', 'SmsOnlineController@getPhoneNumber')->name('get.number');
    Route::get('cn', 'SmsOnlineController@getSmsContent')->name('get.content');
});


Route::group(['prefix' => 'cfcc', 'namespace' => 'Admin'], function ($router) {
    $router->get('logout', 'LoginController@logout')->name('cfcc.logout');
    $router->get('index', 'IndexController@index')->name('cfcc.index');
    $router->get('home', 'IndexController@home')->name('cfcc.home');
    $router->any('index/edit/{id?}', 'IndexController@edit')->name('index.edit');
    $router->get('bal', 'IndexController@bal')->name('cfcc.bal');
    $router->get('show_detail', 'IndexController@show_detail')->name('cfcc.show_detail');
    $router->get('flush', 'IndexController@bal')->name('flush');
    $router->post('test', 'IndexController@test')->name('cfcc.test');
    $router->post('set_bal', 'IndexController@set_bal')->name('cfcc.set_bal');
    $router->any('set_money', 'IndexController@set_money')->name('cfcc.set_money');
    $router->post('searchContent', 'IndexController@search_content')->name('cfcc.searchContent');
    $router->post('searchUserContent', 'IndexController@searchUserContent')->name('cfcc.searchUserContent');
    $router->any('showContents', 'IndexController@showContents')->name('cfcc.showContents');
    //月份及时间
    $router->any('month_detail', 'IndexController@month_detail')->name('cfcc.month_detail');
    //筛选数据
    $router->get('to_filter_detail', 'IndexController@to_filter_detail')->name('cfcc.to_filter_detail');
    $router->get('ye_filter_detail', 'IndexController@ye_filter_detail')->name('cfcc.ye_filter_detail');
    $router->get('all_filter_detail', 'IndexController@all_filter_detail')->name('cfcc.all_filter_detail');

    $router->any('all_return_detail', 'IndexController@all_return_detail')->name('cfcc.all_return_detail');
    $router->any('filter_phones', 'IndexController@filter_phone')->name('cfcc.filter_phones');
    $router->any('filter_phone_add', 'IndexController@filter_phone_add')->name('cfcc.filter_phone_add');

    $router->any('user_manager', 'UsersController@index')->name('cfcc.user_manager');
    $router->any('user_manager_list', 'UsersController@curd')->name('cfcc.user_manager_list');
    $router->any('user_manager_reset', 'UsersController@reset')->name('cfcc.user_manager_reset');
    $router->any('user_manager_delete', 'UsersController@delete')->name('cfcc.user_manager_delete');

    $router->any('ceshi', 'IndexController@ceshi');

});

Route::group(['prefix' => 'client', 'namespace' => 'Admin'], function () {
    Route::any('/', 'ClientController@index')->name('client.index');
    Route::any('/ceshi', 'ClientController@ceshi')->name('client.ceshi');

});
Route::group(['prefix' => 'ceshi/api'], function () {
    Route::get('key', 'ApiSmsController@key');
    Route::get('content', 'ApiSmsController@transfer');
});

//远程关闭指令
Route::any('manager/api/remote_close', 'ApiSmsController@remote_close');

