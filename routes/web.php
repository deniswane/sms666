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


Auth::routes();
#Route::get('/', 'HomeController@index')->name('home');
Route::get('/','StaticPagesController@home') -> name('home');


Route::get('/private-numbers','StaticPagesController@privateNumbers') -> name('private_numbers');
Route::get('/inactive-numbers','StaticPagesController@inactiveNumbers') -> name('inactive_numbers');
Route::get('/contact','StaticPagesController@contact') -> name('contact');

Route::get('/signup', 'UsersController@create')->name('signup');
// 号码详情页
Route::get('/detail/{number}', 'PhonecController@detailSms') -> name('phone.detail');







