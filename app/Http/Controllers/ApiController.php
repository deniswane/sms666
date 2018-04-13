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
    public function getPhoneNumber() {
        $phone_count = PhoneNumber::all()->count();
        $random = random_int(1, $phone_count);
        $phone = PhoneNumber::findOrFail($random)->phone;
        echo json_encode(array('msg' => $phone));
    }

    public function getSmsContent(Request $request) {
        // 1秒内访问 拒绝

        if (isset($_SESSION['last_request_time']) && time() - $_SESSION['last_request_time'] < 1) {
            $_SESSION['last_request_time'] = time();
            echo json_encode(array('msg' => 'The frequency is too fast'));
            exit;
        }
        // 标识
        $token = $request->token;
        // 手机号
        $phone = $request->phone;
        // 验证token(对应账号有没有钱)
        // 拿手机号的最新短信
        $price = DB::table('prices')->find(1);
        $users = User::where('token', $token)
            ->get();
        if ($users[0]->balance > 1) {
            // 短信是否有更新
            $phone_statuses = DB::select('select * from newest_sms_content where phone = ?', [$phone]);
            if ($phone_statuses[0]->is_changed) {
                $phoneNumber = PhoneNumber::where('phone', $phone)->firstOrFail();
                $content = $phoneNumber->smsContents()
                    ->orderBy('created_at', 'desc')
                    ->take(1)
                    ->get();
                $users[0]->balance = $users[0]->balance - $price['price'];
                $users[0]->updated_at = date('Y-m-d H:i:s');
                $users[0]->save();
                $result = array('msg' => $content[0]
                    ->content);
                // 没有最新短信标记
                $phone_statuses[0]->is_changed = false;
                $phone_statuses[0]->save();
                echo json_encode($result);
            } else {
                echo json_encode(array('msg' => 'or No new text messages'));
            }

        } else {
            echo json_encode(array('msg' => 'Not sufficient funds'));
        }
    }
}
