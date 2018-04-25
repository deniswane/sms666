<?php

namespace App\Http\Controllers;

use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            dump($user);
            $phone_count = PhoneNumber::all()->count();
            $random = random_int(1, $phone_count);
            $phone = PhoneNumber::findOrFail($random)->phone;
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
        if ($user->balance > 1) {
            // 短信是否有更新
            $phone_statuse = DB::table('newest_sms_content')->where('phone', $phone)->first();
            if ($phone_statuse->is_changed) {
                $phoneNumber = PhoneNumber::where('phone', $phone)->firstOrFail();
                $content = $phoneNumber->smsContents()
                    ->orderBy('created_at', 'desc')
                    ->take(1)
                    ->get();
                $user->balance = $user->balance - $price->prices;
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();
                $result = array('code' => 200, 'msg' => $content[0]
                    ->content);
                // 取出后更改标记
                DB::table('newest_sms_content')
                    ->where('phone', $phone)
                    ->update(['is_changed' => false]);
                echo json_encode($result);
            } else {
                echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
            }
        }
        } else {
            echo json_encode(array('code' =>101,'msg' => 'Not sufficient funds'));
        }
    }
}
