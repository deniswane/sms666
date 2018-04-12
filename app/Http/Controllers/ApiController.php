<?php

namespace App\Http\Controllers;

use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends Controller {
    public function getPhoneNumber() {
        //todo 返回手机号接口
        $phone_count = PhoneNumber::all()->count();
        $random = random_int(1, $phone_count);
        $phone = PhoneNumber::findOrFail($random)->phone;
        echo json_encode(array('msg' => $phone));
    }

    public function getSmsContent(Request $request) {
        // 1秒内访问 拒绝

        if(isset($_SESSION['last_request_time']) && time()-$_SESSION['last_request_time'] < 1){
            $_SESSION['last_request_time'] = time();
            echo json_encode(array('msg'=>'The frequency is too fast'));
            exit;
        }
        // 标识
        $token = $request->token;
        // 手机号
        $phone = $request->phone;
        // 验证token(对应账号有没有钱)
        // 拿手机号的最新短信
        $user = User::where('token', $token)
            ->get();
        if ($user[0]->balance > 1) {
            $phoneNumber = PhoneNumber::where('phone', $phone)->firstOrFail();
            $content = $phoneNumber->smsContents()
                ->orderBy('created_at', 'desc')
                ->take(1)
                ->get();
            $user[0]->balance = $user[0]->balance - PRICE;
            $user[0]->updated_at = date('Y-m-d H:i:s');
            $user[0]->save();
            $result = array('msg' => $content[0]
                ->content);
            echo json_encode($result);
        } else {
            echo json_encode(array('msg' => 'not sufficient funds'));
        }
    }
}
