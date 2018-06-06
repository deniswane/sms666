<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Mail;

/**
 * Class ApiController
 * @package App\Http\Controllers
 * 是否有新短信标记
 */
class ApiController extends Controller
{
    private $database;
    public function __construct()
    {
        $prefix=(request()->route()->getAction())['prefix'];
        if(strpos($prefix,'inside')){
            $this->database='mysql';
        }else{
            $this->database='';
        }

    }
//    设置关键字
    public function setKeyword(Request $request)
    {
        $get = ['k'=>htmlspecialchars($request->get('k'))];

        $validator = Validator::make($get, [
            'k'=>'required|min:2|max:5'
        ]);

        if ($validator->fails()) {
            echo json_encode(['code'=>102,'msg'=>'Format error']);die;
        }
        if (strpos($request->k, ':') || strpos($request->k, '：')) {

            $token = $request->token;
            $user = $this->selectuser($token);

            $keywords = strpos($request->k, ':') ? explode(":", $request->k) : explode("：", $request->k);
            $receive = '15510396471';
            if ($user) {
                if ($user->balance <=0){
                    echo json_encode(array('code' => 106, 'msg' => 'You need to charge money'));
                    die;
                }
                //余额剩10发邮件
//                $this->balance_info($user);

                //有权限访问
                $content = 'id' . $user->id;

                //激活手机号及设备信息
                $send_phones = DB::table('web_sms_prepare')->where('send','=',0)->orderby('addtime', 'desc')->first();
                if(!$send_phones){
                    Mail::send('emails.excpetion', ['content' => '手机号没有可以被使用的了'], function ($message) {
                        $message->to('641268939@qq.com', 'Email Message')->subject('注意！ 注意!查看‘w_register’表');
                        $message->to('947848875@qq.com', 'Email Message')->subject('注意！ 注意!查看‘w_register’表');
                    });
                    echo json_encode(['code'=>'107','msg'=>'No mobile phone number for the time being']);die;
                }
                DB::table('web_sms_prepare')->where('id',$send_phones->id)->update(['send'=>5]);

//            $mydata = DB::connection('jm_cms')->table('cms_device_data')->where('phone', $send_phones->phone)->first();
//            if (!$mydata) {
//                $mydata = DB::connection('ourcms')->table('cms_device_data')->where('phone', $send_phones->phone)->first();
//            }

                # 有大写问题
                $key = array('a' => 'o', 'b' => '0', 'c' => 'p', 'd' => '1', 'e' => 'q', 'f' => '2', 'g' => 'r', 'h' => '3', 'i' => 's', 'j' => '4', 'k' => 't', 'l' => '5', 'm' => 'u', 'n' => '6', 'o' => 'v', 'p' => '7', 'q' => 'w', 'r' => '8', 's' => 'x', 't' => '9', 'u' => 'y', 'v' => '*', 'w' => 'z', 'x' => '#', 'y' => '&', 'z' => ',', '0' => 'n', '1' => 'm', '2' => 'l', '3' => 'k', '4' => 'j', '5' => 'i', '6' => 'h', '7' => 'g', '8' => 'f', '9' => 'e', '*' => 'd', '#' => 'c', ',' => 'b', '&' => 'a', ':' => '!');


                $_smstext = "second:" . $receive . "&" . $content . "&1&";
                $smstxt = '';
                for ($i = 0; $i < strlen($_smstext); $i++) {
                    $smstxt .= $key[$_smstext[$i]];   # 转换为 ‘密文’
                }

                # 关键字
                $gjz = "$keywords[0]&$keywords[1]&回复到号码:&$keywords[0]&$keywords[1]";

                $gjz=json_encode($gjz);
                $gjz = str_replace("\u", "%u", $gjz);
                $gjz = trim($gjz, '"');
                # 把关键词 添加到了 ‘密文’ 后边
                $smstxt .= $gjz;

                //微信订单状态 0暂停 1开启
                $switch = 1;

                //添加到订单
                //订单存放的库

                //添加到订单
                $tablename = "SMS" . date('Ymd') . '6666';
                $order_res = DB::connection('ourcms')->table('cms_order')
                    ->where('order_name', '=', $tablename)
                    ->where('state', '!=', '-1')
                    ->where('state', '!=', '-2')
                    ->first();
                if (!$order_res) {
                    //新建
                    //获取订单总表存储数据
                    $info = array();
                    $info['order_name'] = $tablename;
                    $info['order_tnum'] = 1;
                    $info['order_num'] = 0;
                    $info['state'] = $switch;//1 open
                    $info['type'] = 10;  //短信订单
                    $info['addtime'] = time();
                    $info['LateSendTime'] = $info['LateReturnTime'] = date("Y-m-d H:i:s");
                    $info['spnumber'] = '';
                    $info['note'] = " 接收短信订单 ";
                    $id = DB::connection('ourcms')->table('cms_order')->insertGetId($info);
                    //创建订单详细表
                    //手机号,指令,发送手机号,发送时间,发送状态(012),用户project,software,返回时间
                    $ordtb = "cms_orddata_" . $id;

                    Schema::connection('ourcms')->create($ordtb, function (Blueprint $table) {
                            $table->charset = 'utf8';
                            $table->engine = 'MyISAM';
                            $table->increments('id');
                            $table->string('phone', 20)->default('')->comment('订单电话号');
                            $table->string('smstext', 255)->default('')->comment('指令回复内容');
                            $table->string('ordimsi', 50)->default('')->comment('订单imsi');
                            $table->string('addtime', 30)->default('')->comment('访问时间');
                            $table->string('userphone', 20)->default('')->comment('访问手机号');
                            $table->string('imsi', 50)->default('')->comment('访问imsi');
                            $table->integer('state')->default(0)->comment('1发送0未发送');
                            $table->string('provinceid', 3)->default('0')->comment('省份id');
                            $table->string('cityid', 3)->default('0')->comment('城市id');
                            $table->string('projectid', 4)->default('0')->comment('项目号');
                            $table->string('software', 200)->default('')->comment('项目号');
                            $table->tinyInteger('is_return')->default(0)->comment('0未返回1返回');
                            $table->string('return_times', 3)->default('0')->comment('返回时长');
                            $table->string('backtime', 30)->default('')->comment('返回时间');
                            $table->tinyInteger('msg')->default(0)->comment('是否有短信返回');
                            $table->string('msgdate', 30)->default('')->comment('短信接收时间');
                            $table->string('spnumber', 30)->default('')->comment('通道号');
                            $table->string('nowtime', 30)->default('')->comment('自动订单添加时间');
                            $table->string('stype', 3)->default('')->comment('短信二次业务回复类型');
                            $table->string('sctstart', 100)->default('')->comment('验证码关键前半部分');
                            $table->string('sctend', 100)->default('')->comment('验证码关键后半部分');
                            $table->string('snumstart', 100)->default('')->comment('回复到某号码的关键半部分');
                            $table->string('maskkeyone', 100)->default('')->comment('关键字1');
                            $table->string('maskkeytwo', 100)->default('')->comment('关键字2');
                        });
                        $new_data = [
                            'phone' => $send_phones->phone,
                            'smstext' => $smstxt,
//                        'ordimsi'=>$mydata['imsi'],
//                        'projectid'=>$mydata['projectid'],
//                        'provinceid'=>$mydata['provinceid'],
                            'nowtime' => date("Y-m-d H:i:s"),
                            'software' => '',

                        ];
                        DB::connection('ourcms')->table($ordtb)->insert($new_data);

                    //记录日志
                    $ip = $request->getClientIp();
                    $txt = Carbon::now() . '   ' . $user->email . '--' . $ip . '--' . $keywords[0] . $keywords[1] . '--success';
                    Storage::disk('local')->append('set_gjz.txt', $txt);
                    echo json_encode(['code' => '200', 'msg' => 'success']);
                    die;
                } else {

                    //追加
                    # 订单总表不是空 追加进去
                    $info = array();
                    $info['order_tnum'] = $order_res->order_tnum + 1; # 订单 总表 里的订单数据 自增 1
                    $info['state'] = $switch;//1 open
                    $info['addtime'] = time();
                    //插入数据
                    # 订单总表里的id 对应 外边订单详细表的表名
                    $ordtb = "cms_orddata_" . $order_res->id;
                    # 成功之后的订单不管了
                    $new_data = [
                        'phone' => $send_phones->phone,
                        'smstext' => $smstxt,
//                        'ordimsi'=>$mydata['imsi'],
//                        'projectid'=>$mydata['projectid'],
//                        'provinceid'=>$mydata['provinceid'],
                        'nowtime' => date("Y-m-d H:i:s"),
                        'software' => '',

                    ];
                    $res =DB::connection('ourcms')->table($ordtb)->insert($new_data);
                    if ($res) {
                        DB::connection('ourcms')->table('cms_order')
                            ->where('id', '=', "$order_res->id")
                            ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
                    }
                    //记录日志
                    $ip = $request->getClientIp();
                    $txt = Carbon::now() . '   ' . $user->email . '--' . $ip . '--' . $keywords[0] . $keywords[1] . '--success';
                    Storage::disk('local')->append('set_gjz.txt', $txt);
                    echo json_encode(['code' => 200, 'msg' => 'success']);
                    die;
                }
            } else {
                //无权访问
                echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));
                die;
            }
        } else {
            return ['code' => 102, 'msg' => 'Format error'];
        }
    }

//    获取手机号
    public function getPhoneNumber(Request $request)
    {

        $token = $request->token;

        $user = $this->selectuser($token);

        if ($user) {
            //余额剩10发邮件
//            $this->balance_info($user);
//            if ($user->times < 5) {
                //获取手机号
                $phone = DB::table('phone_numbers')
                    ->where('user_id', '=', $user->id)
                    ->where('status', '=', '0')
                    ->orderby('created_at', 'desc')
                    ->first();
                if(!$phone){
                    echo json_encode(['code'=>'107','msg'=>'No mobile phone number for the time being']);die;
                }
                DB::table('phone_numbers')->where('id',$phone->id)->update(['status'=>'1']);

//                $new_time = $user->times + 1;
//                DB::table('users')
//                    ->where('id', '=', "$user->id")
//                    ->update(['times' => $new_time]);
                //日志
                $ip = $request->getClientIp();
                $txt = Carbon::now() . '   ' . $user->email . '--' . $ip . '--' . $phone->phone;
                Storage::disk('local')->append('get_phone.txt', $txt);
                echo json_encode(array('code' => 200, 'msg' => $phone->phone));
                die;
//            } else {
//                echo json_encode(array('code' => 201, 'msg' => 'Please update your text message first'));
//                die;
//            }
        } else {

            echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));die;
        }
    }

    public function getSmsContent(Request $request)
    {
        // 标识
        $token = $request->token;
        // 手机号
        $get_phone =[ 'phone'=>$request->phone];
        //验证手机号

        $validator = Validator::make($get_phone, [
            'phone'=>'required |regex:/^1[34578][0-9]{9}$/'
        ]);

        if ($validator->fails()) {
            echo json_encode(['code'=>102,'msg'=>'Format error']);die;
        }
        // 验证token(对应账号有没有钱)
        // 拿手机号的最新短信
        $phone=$request->phone;
        $user = $this->selectuser($token);
        if ($user) {
            //余额剩10发邮件
//            $this->balance_info($user);

            if ($user->balance > 0) {
                $phoneNumber =DB::table('phone_numbers')
                    ->where('user_id',$user->id)
                    ->where('status','1')
                    ->where('phone',$phone)
                    ->first();

                if ($phoneNumber) {

                    $content = DB::table('sms_contents')
                        ->where('phone_number_id',$phoneNumber->id)
                        ->orderby('created_at','desc')
                        ->first();
                    if(!$content){
                        echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
                        die;
                    }

                    $price = DB::table('configs')->find(1);
                    $new_balbance=$user->balance - $price->price;

                    $update_time=date('Y-m-d H:i:s');

                    DB::table('users')
                        ->where('id', '=', $user->id)
                        ->update(['updated_at' => $update_time,'balance'=>$new_balbance]);

                    $result = array('code' => 200, 'msg' => $content->content);

                    //记录日志
                    $ip = $request->getClientIp();
                    $txt = Carbon::now() . '   ' . $user->email . '--' . $ip . '--' . $phone . '--' .$content->content .'--'.$user->balance;
                    Storage::disk('local')->append('get_content.txt', $txt);

                    //统计访问量
                    $amount = DB::table('page_views')->where('user_id', $user->id)->first();
                    if ($amount) {
                        $amounts = $amount->amounts + 1;
                        if ((time() - strtotime($amount->expiration_time)) >= 0) {
                            $data['expiration_time'] = date('Y:m:d H-i-s', strtotime(date('Y-m-d', time())) + 86400);
                            $data['daliy_amount'] = 1;
                            $data['amounts'] = $amounts;
                        } else {
                            $data['daliy_amount'] = $amount->daliy_amount + 1;
                            $data['amounts'] = $amounts;
                            $data['expiration_time'] = $amount->expiration_time;
                        }
                        DB::table('page_views')->where('user_id', $user->id)->update($data);
                        echo json_encode($result,JSON_UNESCAPED_UNICODE);die;

                    } else {
                        DB::table('page_views')->insert(['user_id' => $user->id, 'daliy_amount' => 0, 'amounts' => 0]);
                    }
                } else {
                    echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
                    die;
                }

            } else {
                echo json_encode(array('code' => 106, 'msg' => 'You need to charge money'));
                die;
            }
        } else {
            echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));
            die;
        }
    }

    //查用户
    private function selectuser($token)
    {
        return DB::table('users')
            ->select('balance','email','id','balance')
            ->where('token',$token)
            ->first();

    }
//    private function balance_info($user){
//        if ($user ->balance =10){
//            Mail::send('emails.excpetion', ['content' => 'Your balance is 10 dollars left. Please pay attention to the recharge'], function ($message) use ($user) {
//                $message->to($user->email, 'Email Message')->subject('BALANCE INFO');
//            });
//        }
//
//    }


}
