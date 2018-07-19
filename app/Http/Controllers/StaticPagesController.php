<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StaticPagesController extends Controller
{
    public function __construct() {

        $this->middleware('auth',['only' => ['api','download']]);
    }

    // 获取首页数据，电话
    public function home(Request $request){
        switch ($_SERVER['HTTP_HOST']){
            case 'sms.test':
                App::setLocale('zh-CN');
                $lang ='zh-CN' ;
                break;
            default :
                App::setLocale('en');
                $lang ='en' ;
                break;
        }
        $numbers =DB::table('phone_numbers')
            ->select('phone_numbers.id','phone_numbers.phone','phone_numbers.province','phone_numbers.amount','flages.src')
            ->leftjoin('flages','phone_numbers.province','=','flages.en_name')
            ->paginate(10);
        return view('welcome_new',compact(['numbers','lang']))->__toString();
    }

    public function getprice()
    {
        $prices = DB::table('configs')->select('price')->find(1);
        return response()->json($prices);
    }
    public function privateNumbers(){
        return view('layouts.private_number')->__toString();
    }

    public function api()
    {
        $user =Auth::user();
        $token=$user->token;
        return view('layouts.api',compact('token'))->__toString();

    }
    public function inactiveNumbers(){
        echo "inactiveNumbers";
    }

    public function contact(){
        dd($_SERVER['HTTP_HOST']);
        echo "contact";
    }

    public function setresult(Request $request)
    {
        $error=$request->error;
        return view('emails.verification_result',['error'=>$error]);
    }

    public function download(Request $request ){
        $user =Auth::user();

        $date= $request->date;

        if (is_file(storage_path('app').'/'.$date.'/'.$user->name.'/'.$date.'.txt')){
            return response()->download(storage_path('app').'/'.$date.'/'.$user->name.'/'.$date.'.txt');
//
        }else{
            return response()->download(storage_path('app').'/result.txt');
        }
    }
}
