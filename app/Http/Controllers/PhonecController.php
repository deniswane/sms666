<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhoneNumber;

class PhonecController extends Controller
{
    // 获取首页数据，电话
    public function home(){
        $numbers = PhoneNumber::paginate(20);
        return view('welcome',compact('numbers'));
    }
    //
    public function detailSms($token){
        echo $token;
    }

}
