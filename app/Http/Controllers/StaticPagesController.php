<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhoneNumber;

class StaticPagesController extends Controller
{
    // 获取首页数据，电话
    public function home(){
        $numbers = PhoneNumber::paginate(20);
        return view('welcome',compact('numbers'));
    }

    public function privateNumbers(){
        echo "privateNumbers";
    }

    public function inactiveNumbers(){
        echo "inactiveNumbers";
    }

    public function contact(){
        echo "contact";
    }
}
