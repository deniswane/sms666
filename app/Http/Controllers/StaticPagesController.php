<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhoneNumber;
use Illuminate\Support\Facades\DB;

class StaticPagesController extends Controller
{
    public function __construct() {

        $this->middleware('auth',['only' => ['privateNumbers']]);
    }

    // 获取首页数据，电话
    public function home(){

        $numbers =DB::table('phone_numbers')
            ->select('phone_numbers.id','phone_numbers.phone','phone_numbers.country','phone_numbers.amount','flages.src')
            ->leftjoin('flages','phone_numbers.country','=','flages.en_name')
            ->paginate(30);

        return view('welcome_new',compact('numbers'));
    }

    public function privateNumbers(){
        return view('layouts.private_number');
    }

    public function inactiveNumbers(){
        echo "inactiveNumbers";
    }

    public function contact(){
        echo "contact";
    }

    public function setresult(Request $request)
    {
        $error=$request->error;
        return view('emails.verification_result',['error'=>$error]);
    }
}
