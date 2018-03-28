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
Route::get('/payment/success','PaymentController@success') ->name('success');
Route::get('/payment/fail','PaymentController@fail') ->name('fail');
Route::get('/payment/status','PaymentController@status') ->name('status');







