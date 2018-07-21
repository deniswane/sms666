<?php

namespace App\Http\Controllers\Admin;

use App\Library\Y;
use App\Models\SmsContent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $post = $request->all();
            $validator = Validator::make($post, [
                'phone' => 'required|regex:/^1[34578][0-9]{9}$/',
            ]);

            if ($validator->fails()) {
                return Y::error($validator->errors());
            }



            $phone = $request->phone;
            $userid= Auth::id();


            $phoneNumber = DB::table('phone_numbers')
                ->select('id')
                ->where('phone', $phone)
                ->where('user_id', $userid)
                ->first();
            if ($phoneNumber) {
                $contents= SmsContent::leftjoin('phone_numbers','phone_numbers.id','=','sms_contents.phone_number_id')
                    ->where('phone',$phone)->where('content','like','%验证%')->where(function ($query){
                        $query->where('content','like','%京东%')
                            ->Orwhere('content','like','%淘宝%');
                    })->orderby('sms_contents.updated_at','desc')
                    ->get()->toarray();
                if ($contents) {
                    $str='';
                    foreach ($contents as $content){
                       $str.= $content['updated_at'] . '<br>' . $content['content'].'<br>';
                    }
                    return $str;

                } else {
                    return 'no message';
                }
            } else {
                return 'no message';
            }

        }
        return view('client.index');
    }
}
