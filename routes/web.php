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
#Route::get('/', 'HomeController@index')->name('home');
Route::get('/','StaticPagesController@home') -> name('home');
// 多语言
Route::get('/language/{locale}', ['as'=>'lang.change', 'uses'=>'LanguageController@setLocale']);
//Route::get('/language/{locale}', function ($locale) {
//    App::setLocale($locale);
//    return redirect()->route('home');
//});

Route::get('/private-numbers','StaticPagesController@privateNumbers') -> name('private_numbers');
Route::get('/inactive-numbers','StaticPagesController@inactiveNumbers') -> name('inactive_numbers');
Route::get('/contact','StaticPagesController@contact') -> name('contact');

Route::get('/signup', 'UsersController@create')->name('signup');
//Route::any('/signup', 'UsersController@create')->name('signup');
// 注册确认
//此方法是 VerifiesUsers Trait 里的方法，他会自动处理验证逻辑
Route::get('/verification/{token}','Auth\RegisterController@getVerification');
Route::get('/emails/verification_result','StaticPagesController@setresult');
//Route::get('/emails/verification_result','Auth\RegisterController@setresult');
// 号码详情页
Route::get('/detail/{number}', 'PhonecController@detailSms') -> name('phone.detail');
Route::get('/getprice', 'StaticPagesController@getprice') -> name('getprice');

// 支付链接
Route::view('/payment/recharge','pay/recharge');
Route::post('/payment/recharge','PaymentController@launchPay') -> name('launch');
Route::get('/payment/success','PaymentController@success') ->name('success');
Route::get('/payment/fail','PaymentController@fail') ->name('fail');
Route::get('/payment/status','PaymentController@status') ->name('status');

// 接口 十分钟请求三次
Route::group(['prefix' => 'manager/api','middleware' => 'throttle'], function () {
    Route::get('getPhoneNumber','ApiController@getPhoneNumber') ->name('get.number');
    Route::get('getSmsContent','ApiController@getSmsContent') ->name('get.content');
});


Route::group(['prefix' => 'cfcc','namespace' => 'Admin'],function ($router)
{
    $router->get('logout', 'LoginController@logout')->name('cfcc.logout');
    $router->get('index', 'IndexController@index')->name('cfcc.index');
    $router->get('bal', 'IndexController@bal')->name('cfcc.bal');
    $router->post('test', 'IndexController@test')->name('cfcc.test');
    $router->get('flush', 'IndexController@flush') ->name('flush');
    $router->post('phone_info', 'IndexController@phone_info')->name('cfcc.phone_info');
    $router->any('set_money', 'IndexController@set_money')->name('cfcc.set_money');

});
//登陆、密码修改
Route::any('/me', 'Admin\AdminController@me')->name('me');
Route::any('cfcc/login', 'Admin\LoginController@login')->name('cfcc.login');

//paypal
Route::any('paypal/ec-checkout', 'PayPalController@getExpressCheckout')->name('ec-checkout');
Route::get('paypal/ec-checkout-success', 'PayPalController@getExpressCheckoutSuccess');
Route::get('paypal/adaptive-pay', 'PayPalController@getAdaptivePay');
Route::post('paypal/notify', 'PayPalController@notify');
Route::get('paypal/success', 'PayPalController@success');





