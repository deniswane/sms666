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

// 号码详情页
Route::get('/{token}', 'PhonecController@detailSms')->name('phone.detail');

Route::get('/','PhonecController@home') -> name('home');
