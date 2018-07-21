<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $prefix = (request()->route()->getAction())['prefix'];
        if (strpos($prefix, 'inside')) {
            $this->database = 'mysql';
        } else {
            $this->database = '';
        }

    }

    /**设置关键字
     * @param Request $request
     * @return array
     */
    public function setKeyWord(Request $request)
    {
        //数据验证
        $get = ['k' => htmlspecialchars($request->get('k')), 'p' => $request->get('p')];
        $validator = Validator::make($get, [
            'k' => 'required|min:2|max:5',
//            'p'=>'required'
        ]);
        if ($validator->fails()) {
            echo json_encode(['code' => 102, 'msg' => 'Format error']);
            die;
        }
        $p = $request->get('p');

        if (strpos($request->k, ':') || strpos($request->k, '：')) {

            $token = $request->token;
            $user = $this->selectuser($token);

            $keywords = strpos($request->k, ':') ? explode(":", $request->k) : explode("：", $request->k);
//            $receive = '15932011375';
            $receives = ['13061195162', '15932011375', '13235364220'];
            $receive = $receives[array_rand($receives)];

            if ($user) {
                if ($user->balance <= 0) {
                    echo json_encode(array('code' => 106, 'msg' => 'You need to charge money'));
                    die;
                }
                //有权限访问 返回的标记
                $content = 'id' . $user->id;
                if ($user->id == '12') {//四川省份设置关键字成功概率限制目前70%概率会设置成功
                    $prize_arr = array(
                        '0' => array('id' => 1, 'v' => 20),
                        '1' => array('id' => 2, 'v' => 30),
                        '2' => array('id'=>3,'v'=>20),
                        '3' => array('id'=>4,'v'=>10),
                        '4' => array('id'=>5,'v'=>10),
                        '5' => array('id'=>6,'v'=>10),
                    );
                    foreach ($prize_arr as $key => $val) {
                        $arr[$val['id']] = $val['v'];
                    }
                    $rid = $this->get_rand($arr);
                    if ($rid == 2) {
                        echo json_encode(['code' => '200', 'msg' => 'success']);
                        die;
                    }
                }
//              不论哪个用户在设置关键字时，返回用户的id从[12,22]中按概率选一个
//                $prize_arr = array(
//                    '0' => array('id' => 1, 'v' => 40),
//                    '1' => array('id' => 2, 'v' => 60),
//
//                );
//                foreach ($prize_arr as $key => $val) {
//                    $arr[$val['id']] = $val['v'];
//                }
//                $rid = get_rand($arr);
//                $id =$rid== 1?'12':'22';
//                $content = 'id' . $id;


                $dat = empty($p) ? ['send' => 0] : ['send' => 0, 'province' => $p];

                $phone = $this->filter($dat);

                if (!$phone) {
                    echo json_encode(['code' => '107', 'msg' => 'No mobile phone number for the time being']);
                    die;
                }
                //setorder($phone,$content,$receive,$gjz)
                $gjz = "$keywords[0]&$keywords[1]&回复到号码:&$keywords[0]&$keywords[1]";

                //写入日志的信息
                $ip = $request->getClientIp();
                $profile = $user->name . '/set_gjz.txt';
                $data = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'keywords' => "$keywords[0]&$keywords[1]",
                    'provice' => $p,
                    'success' => 'success'
                ];

                $this->setorder($phone, $content, $receive, $gjz, $profile, $data);

            } else {
                //无权访问
                echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));
                die;
            }
        } else {
            return ['code' => 102, 'msg' => 'Format error'];
        }
    }

    /**获取手机号
     * @param Request $request
     */
    public function getPhoneNumber(Request $request)
    {
        //验证数据
        $token = $request->token;
        $get_p = ['p' => $request->p];
        $validator = Validator::make($get_p, [
//            'p' =>'required'
        ]);
        if ($validator->fails()) {
            echo json_encode(['code' => 102, 'msg' => 'Format error']);
            die;
        }

        $p = $request->p;

        $user = $this->selectuser($token);

        if ($user) {

            $dat = empty($p) ? ['user_id' => $user->id, 'status' => '0'] : ['user_id' => $user->id, 'status' => '0', 'province' => $p];

            $phone = DB::table('phone_numbers')
                ->select('phone', 'id')
                ->where($dat)
                ->orderby('created_at', 'desc')
                ->first();
            if (!$phone) {
                echo json_encode(['code' => '107', 'msg' => 'No mobile phone number for the time being']);
                die;
            }
            DB::table('phone_numbers')->where('id', $phone->id)->update(['status' => '1']);

            //日志
            $ip = $request->getClientIp();
            $profile = $user->name . '/get_phone.txt';
            $data = [
                'email' => $user->email,
                'ip' => $ip,
                'phone' => $phone->phone,
                'province' => $p,
            ];
            $this->setLog($profile, $data);

            echo json_encode(array('code' => 200, 'msg' => $phone->phone));
            die;
//            } else {
//                echo json_encode(array('code' => 201, 'msg' => 'Please update your text message first'));
//                die;
//            }
        } else {

            echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));
            die;
        }
    }

    /**获取短信内容
     * @param Request $request
     */
    public function getSmsContent(Request $request)
    {
        // 验证数据
        $token = $request->token;

        $get_phone = ['phone' => $request->phone];


        $validator = Validator::make($get_phone, [
            'phone' => 'required |regex:/^1[34578][0-9]{9}$/'
        ]);

        if ($validator->fails()) {
            echo json_encode(['code' => 102, 'msg' => 'Format error']);
            die;
        }
        // 验证token(对应账号有没有钱)
        // 拿手机号的最新短信
        $phone = $request->phone;
        $user = $this->selectuser($token);
        if ($user) {

            if ($user->balance > 0) {
                $phoneNumber = DB::table('phone_numbers')
                    ->select('id')
                    ->where(['user_id' => $user->id, 'status' => '1', 'phone' => $phone])
                    ->orWhere(['phone' => $phone])
                    ->first();

                if ($phoneNumber) {

                    $content = DB::table('sms_contents')
                        ->select('id', 'content', 'status')
                        ->where('phone_number_id', $phoneNumber->id)
                        ->orderby('created_at', 'desc')
                        ->first();
                    if (!$content) {
                        echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
                        die;
                    }

                    //更新取号后的状态
                    DB::table('sms_contents')->where('id', $content->id)->update(['status' => '1', 'updated_at' => Carbon::now()]);


                    $price = DB::table('configs')->select('price')->find(1);
                    $new_balbance = $user->balance - $price->price;

                    $update_time = date('Y-m-d H:i:s');
                    DB::table('users')
                        ->where('id', '=', $user->id)
                        ->update(['updated_at' => $update_time, 'balance' => $new_balbance]);

                    $result = array('code' => 200, 'msg' => $content->content);

                    if ($content->content === 'xuxxq61!p5vxq') {
                        $content = DB::table('sms_contents')
                            ->select('content')
                            ->where('phone_number_id', $phoneNumber->id)
                            ->orderby('created_at', 'asc')
                            ->first();
                        $result = array('code' => 200, 'msg' => $content->content);
                    }


                    if ($content->status != '1') {
                        //记录日志
                        $ip = $request->getClientIp();
                        $contets = [
                            'email' => $user->email,
                            'ip' => $ip,
                            'phone' => $phone,
                            'content' => $content->content,
                            'balance' => $user->balance
                        ];
//                        $profile = $user->name . '/get_content.txt';
                        $profile = $user->name . '/'.date('Ymd',time()).'.txt';

                        $this->setLog($profile, $contets);
                        //统计请求量
                    }
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    die;
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

    /**发送短信
     * @param Request $request
     * @param User $user
     */
    public function sendMsg(Request $request)
    {
        //参数验证
        if ($request->isMethod('post')) {

            $parame = $request->all();
            $validator = Validator::make($parame, [
                'phone' => 'required |regex:/^1[34578][0-9]{9}$/',
                'receive' => 'required |regex:/^1[34578][0-9]{9}$/',
                'content' => 'required',
                'token' => 'required'
            ]);

            if ($validator->fails()) {
                echo json_encode(['code' => 102, 'msg' => 'Format error']);
                die;
            }
            $user = $this->selectuser($parame['token']);

            if ($user) {
                if ($user->balance > 0) {

                    $phon = $parame['phone'];
                    //判断手机号
                    $phoneNumber = DB::table('phone_numbers')
                        ->where('user_id', $user->id)
                        ->where('phone', $phon)
                        ->first();

                    if (!$phoneNumber) {
                        echo json_encode(['code' => '107', 'msg' => 'No mobile phone number for the time being']);
                        die;
                    }


                    $price = DB::table('configs')->find(1);
                    $new_balbance = $user->balance - $price->price;
                    $update_time = date('Y-m-d H:i:s');
                    DB::table('users')
                        ->where('id', '=', $user->id)
                        ->update(['updated_at' => $update_time, 'balance' => $new_balbance]);

                    $profile = $user->name . '/send_msg.txt';
                    $data = [
                        'ip' => $request->getClientIp(),
                        'email' => $user->email,
                        'content' => $parame['content']
                    ];
                    //统计请求量

                    $receive = $parame['receive'];
                    $content = $parame['content'];
                    $gjz = "证码&。请&证码&。请";
                    $this->setorder($phon, $content, $receive, $gjz, $profile, $data);
                }
            }
        }
    }

    /**
     * 筛选手机号
     * return phone
     */
    public function filter($dat)
    {
        $send_phones = DB::table('web_sms_prepare')->select('phone', 'id')->where($dat)->orderby('addtime', 'desc')->first();
        if (!$send_phones) {
            return false;
        }
        DB::table('web_sms_prepare')->where('id', $send_phones->id)->update(['send' => 5]);

        return $send_phones->phone;
    }

    /*设置订单
     * phone    发送的手机号
     * content  返回的关键字
     * receive  接收的手机号
     * gjz      返回截取的位置
     */
    public function setorder($phone, $content, $receive, $gjz, $profile, $parame, $type = 0)
    {

//        $mydata = DB::connection('jm_cms')->table('cms_device_data')->where('phone', $phone)->first();
//         if (!$mydata)
//         {
//             $mydata = DB::connection('ourcms')->table('cms_device_data')->where('phone', $phone)->first();
//         }
        # 有大写问题
        $key = array('a' => 'o', 'b' => '0', 'c' => 'p', 'd' => '1', 'e' => 'q', 'f' => '2', 'g' => 'r', 'h' => '3', 'i' => 's', 'j' => '4', 'k' => 't', 'l' => '5', 'm' => 'u', 'n' => '6', 'o' => 'v', 'p' => '7', 'q' => 'w', 'r' => '8', 's' => 'x', 't' => '9', 'u' => 'y', 'v' => '*', 'w' => 'z', 'x' => '#', 'y' => '&', 'z' => ',', '0' => 'n', '1' => 'm', '2' => 'l', '3' => 'k', '4' => 'j', '5' => 'i', '6' => 'h', '7' => 'g', '8' => 'f', '9' => 'e', '*' => 'd', '#' => 'c', ',' => 'b', '&' => 'a', ':' => '!');


        if ($type == '1') {
            //单独指令开关
            $_smstext = 'smssend:open&' . $receive;
            $smstxt = '';
            for ($i = 0; $i < strlen($_smstext); $i++) {
                $smstxt .= $key[$_smstext[$i]];   # 转换为 ‘密文’
            }

        } else {
            $_smstext = "second:" . $receive . "&" . $content . "&1&";
            $smstxt = '';
            for ($i = 0; $i < strlen($_smstext); $i++) {
                $smstxt .= $key[$_smstext[$i]];   # 转换为 ‘密文’
            }

            # 关键字
            $gjz = json_encode($gjz);
            $gjz = str_replace("\u", "%u", $gjz);
            $gjz = trim($gjz, '"');
            # 把关键词 添加到了 ‘密文’ 后边
            $smstxt .= $gjz;
        }
        //短信订单状态 0暂停 1开启
        $switch = 1;

        //添加到订单
        //订单存放的库

        //添加到订单
        $tablename = "SMS" . date('Ymd') . '6666';
        $order_res = DB::connection('ourcms')->table('cms_order')
//        $order_res = DB::table('cms_order')
            ->select('id', 'order_tnum')
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
//            $id = DB::table('cms_order')->insertGetId($info);
            $id = DB::connection('ourcms')->table('cms_order')->insertGetId($info);
            //创建订单详细表
            //手机号,指令,发送手机号,发送时间,发送状态(012),用户project,software,返回时间
            $ordtb = "cms_orddata_" . $id;
            Schema::connection('ourcms')->create($ordtb, function (Blueprint $table) {
//            Schema::create($ordtb, function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->engine = 'MyISAM';
                $table->increments('id');
                $table->string('phone', 20)->default('')->comment('订单电话号');
                $table->string('smstext', 255)->comment('指令回复内容');
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
                'phone' => $phone,
                'smstext' => $smstxt,
//                        'ordimsi'=>$mydata['imsi'],
//                        'projectid'=>$mydata['projectid'],
//                        'provinceid'=>$mydata['provinceid'],
                'nowtime' => date("Y-m-d H:i:s"),
                'software' => '',

            ];
            DB::connection('ourcms')->table($ordtb)->insert($new_data);
//            DB::table($ordtb)->insert($new_data);

            //记录日志
            $this->setLog($profile, $parame);

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
                'phone' => $phone,
                'smstext' => $smstxt,
//                        'ordimsi'=>$mydata['imsi'],
//                        'projectid'=>$mydata['projectid'],
//                        'provinceid'=>$mydata['provinceid'],
                'nowtime' => date("Y-m-d H:i:s"),
                'software' => '',

            ];
            $res = DB::connection('ourcms')->table($ordtb)->insert($new_data);
//            $res = DB::table($ordtb)->insert($new_data);
            if ($res) {
                DB::connection('ourcms')->table('cms_order')
//                DB::table('cms_order')
                    ->where('id', '=', "$order_res->id")
                    ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
            }
            //记录日志
            $this->setLog($profile, $parame);

            echo json_encode(['code' => '200', 'msg' => 'success']);
            die;

        }
    }

    /**查用户
     * @param $token
     * @return mixed
     */
    public function selectuser($token)
    {
        return DB::table('users')
            ->select('token', 'id', 'email', 'balance', 'name', 'times')
            ->where('token', $token)
            ->first();

    }

    /**记日志
     * @param $profile
     * @param array $parame
     */
    public function setLog($profile, $parame = [])
    {
        $dt = Carbon::now();
        $txt = $dt . '   ' . implode('--', $parame);
        $day = date('Ymd', time());
        Storage::disk('local')->append($day . '/' . $profile, $txt);
    }

    /**设置关键字成功概率
     * @param $proArr
     * @return int|string
     */
    private function get_rand($proArr)
    {
        $result = '';
//
//            //概率数组的总概率精度
        $proSum = array_sum($proArr);
//            //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
//                //随机选取一个数字，符合则中端输出
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {  //缩小概率精度
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
//
        return $result;
    }
}