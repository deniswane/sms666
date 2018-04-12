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


Route::get('/private-numbers','StaticPagesController@privateNumbers') -> name('private_numbers');
Route::get('/inactive-numbers','StaticPagesController@inactiveNumbers') -> name('inactive_numbers');
Route::get('/contact','StaticPagesController@contact') -> name('contact');

Route::get('/signup', 'UsersController@create')->name('signup');
// 注册确认
//此方法是 VerifiesUsers Trait 里的方法，他会自动处理验证逻辑
Route::get('/verification/{token}','Auth\AuthController@getVerification');
// 号码详情页
Route::get('/detail/{number}', 'PhonecController@detailSms') -> name('phone.detail');

// 支付链接
Route::view('/payment/recharge','pay/recharge');
Route::post('/payment/recharge','PaymentController@launchPay') -> name('launch');
Route::get('/payment/success','PaymentController@success') ->name('success');
Route::get('/payment/fail','PaymentController@fail') ->name('fail');
Route::get('/payment/status','PaymentController@status') ->name('status');

// 接口
Route::get('/manager/api/getPhoneNumber','ApiController@getPhoneNumber') ->name('get.number');
Route::get('/manager/api/getSmsContent','ApiController@getSmsContent') ->name('get.content');

//admin

//Route::get('/admin/index','Admin\IndexController@index') ->name('index');
//
//Route::get('/admin/bal','Admin\IndexController@bal') ->name('admin.bal');
//Route::post('/admin/test','Admin\IndexController@test') ->name('admin.test');
//Route::get('/admin/flush','Admin\IndexController@flush') ->name('flush');
//Route::post('/admin/phone_info','Admin\IndexController@phone_info') ->name('admin.phone_info');
//
//后台管理员
Route::group(['prefix' => 'admin','namespace' => 'Admin'],function ($router)
{
    $router->get('login', 'LoginController@showLogin')->name('admin.login');
    $router->post('login', 'LoginController@login');
    $router->get('logout', 'LoginController@logout')->name('admin.logout');
    $router->get('index', 'IndexController@index');

    $router->get('bal', 'IndexController@bal')->name('admin.bal');
    $router->post('test', 'IndexController@test')->name('admin.test');
    $router->get('flush', 'IndexController@flush') ->name('flush');
    $router->post('phone_info', 'IndexController@phone_info')->name('admin.phone_info');
});
Route::any('/me', 'Admin\AdminController@me')->name('me');





