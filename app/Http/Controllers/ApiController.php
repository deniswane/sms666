<?php

namespace App\Http\Controllers;

use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mail;
/**
 * Class ApiController
 * @package App\Http\Controllers
 * 是否有新短信标记
 */
class ApiController extends Controller {
    public function getPhoneNumber(Request $request) {
        $token = $request->token;
        $user = User::where('token', $token)
            ->first();
        if($user){
            $phone_count = PhoneNumber::all()->values('id')->where('status',0)->toArray();
           if (empty($phone_count)){
               echo json_encode(array('code' =>107,'msg' => "No mobile phone number for the time being"));
               Mail::send('emails.excpetion', ['content'=>'手机号没有可以被使用的了'], function($message)
               {
                   $message->to('641268939@qq.com', 'Email Message')->subject('注意！ 注意!');
               });
               die;
           }
            $phone_id= array_rand($phone_count,1)+1;
//            dd($phone_id);
//            $random = random_int(1, $phone_count);
//            $phone = PhoneNumber::findOrFail($random)->where('status',0)->phone;
            $phone= DB::table('phone_numbers')->select('phone')->find($phone_id);

            DB::table('phone_numbers')->where('id',$phone_id)->update(['status'=>'1']);
            $ip=$request->getClientIp();
            Log::write('info', 'get_phone:',['email'=>$user->email,'ip'=>$ip,'phone'=>$phone->phone]);
            echo json_encode(array('code'=>200,'msg' => $phone));
        }else{

            echo json_encode(array('code' =>105,'msg' => "Sorry, sir. You have no right to visit"));
        }
    }

    public function getSmsContent(Request $request) {
        // 1秒内访问 拒绝
        // 标识
        $token = $request->token;
        // 手机号
        $phone = $request->phone;
        // 验证token(对应账号有没有钱)
        // 拿手机号的最新短信
        $price = DB::table('configs')->find(1);
        $user = User::where('token', $token)
            ->first();
        if($user){
            if ($user->balance > 0) {
                // 短信是否有更新
                $phone_statuse = DB::table('newest_sms_content')->where('phone', $phone)->first();
                if($phone_statuse){
                    if ($phone_statuse->is_changed) {
                        $phoneNumber = PhoneNumber::where('phone', $phone)->firstOrFail();
                        $content = $phoneNumber->smsContents()
                            ->orderBy('created_at', 'desc')
                            ->take(1)
                            ->get();
                        $user->balance = $user->balance - $price->price;
                        $user->updated_at = date('Y-m-d H:i:s');
                        $user->save();
                        $result = array('code' => 200, 'msg' => $content[0]
                            ->content);
                        // 取出后更改标记
                        DB::table('newest_sms_content')
                            ->where('phone', $phone)
                            ->update(['is_changed' => false]);

                        DB::table('phone_numbers')
                            ->where('phone', $phone)
                            ->update(['status' =>'0']);
                        //记录日志
                        $ip=$request->getClientIp();
                        Log::write('info', 'get_content:',['email'=>$user->email,'ip'=>$ip,'balance'=>$user->balance,'phone_number'=>$phone]);
                        //统计访问量
                        $amount= DB::table('page_views')->where('user_id',$user->id)->first();
                        if($amount){
                            $amounts = $amount->amounts+1;
                            if((time()-strtotime($amount->expiration_time)) >=0){
                                $data['expiration_time']= date('Y:m:d H-i-s',strtotime(date('Y-m-d', time()))+86400);
                                $data['daliy_amount']   =1;
                                $data['amounts']       = $amounts;
                            }else{
                                $data['daliy_amount']   =$amount->daliy_amount+1;
                                $data['amounts']       =$amounts;
                                $data['expiration_time']=$amount->expiration_time;
                            }
                            DB::table('page_views')->where('user_id',$user->id)->update($data);
                            echo json_encode($result);

                        }else{

                            DB::table('page_views')->insert(['user_id'=>$user->id,'daliy_amount'=>0,'amounts'=>0]);
                        }
                    } else {
                        echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
                    }
                }
            }else{
                echo json_encode(array('code' =>106,'msg' => 'You need to charge money'));

        }
        } else {
            echo json_encode(array('code' =>101,'msg' => 'Not sufficient funds'));
        }
    }

}
