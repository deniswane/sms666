<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Recca0120\Terminal\Console\Commands\Mysql;

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
            ->paginate(10);
        return view('welcome_new',compact('numbers'))->__toString();
    }

    public function getprice()
    {
//        $prices = DB::table('configs')->select('price_i','price_a','num_a','num_i')->find(1);
        $prices = DB::table('configs')->select('price')->find(1);
//        $data=['status'=>200];
//        echo json_encode($data);
        return response()->json($prices);
    }
    public function privateNumbers(){
        return view('layouts.private_number')->__toString();
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
