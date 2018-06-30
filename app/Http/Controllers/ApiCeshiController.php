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
class ApiCeshiController extends Controller
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

//            echo Carbon::now();
//            /* Connect to a MySQL server  连接数据库服务器 */
//            $link = mysqli_connect(
//                '183.196.13.28',  /* The host to connect to 连接MySQL地址 */
//                'website',      /* The user to connect as 连接MySQL用户名 */
//                'bsgs1949sgsb',  /* The password to use 连接MySQL密码 */
//                'receivesms',
//                '12306'
//            );    /* The default database to query 连接数据库名称*/
//
//            if (!$link) {
//                printf("Can't connect to MySQL Server. Errorcode: %s ", mysqli_connect_error());
//                exit;
//            }else {
//
//                echo '数据库连接上了！';
//                echo Carbon::now();
//            }
//            /* Close the connection 关闭连接*/
//            mysqli_close($link);
//        die;

        //数据验证
        $get = ['k'=>htmlspecialchars($request->get('k')),'p'=>$request->get('p')];
        $validator = Validator::make($get, [
            'k'=>'required|min:2|max:5',
//            'p'=>'required'
        ]);
        if ($validator->fails()) {
            echo json_encode(['code'=>102,'msg'=>'Format error']);die;
        }
        $p = $request->get('p');

        if (strpos($request->k, ':') || strpos($request->k, '：')) {

            $token = $request->token;
            $user = $this->selectuser($token);

            $keywords = strpos($request->k, ':') ? explode(":", $request->k) : explode("：", $request->k);
            $receive = '15510396471';
            if ($user) {
                if ($user->balance <= 0) {
                    echo json_encode(array('code' => 106, 'msg' => 'You need to charge money'));
                    die;
                }
                //有权限访问 返回的标记
                $content = 'id' . $user->id;
                $dat=empty($p) ? ['send'=>0]:['send'=>0,'province'=>$p];

                $phone = $this->filter($dat);

                if (!$phone) {
                    echo json_encode(['code' => '107', 'msg' => 'No mobile phone number for the time being']);
                    die;
                }
                //setorder($phone,$content,$receive,$gjz)
                $gjz = "$keywords[0]&$keywords[1]&回复到号码:&$keywords[0]&$keywords[1]";

                //写入日志的信息
                $ip = $request->getClientIp();
                $profile = $user->name.'/set_gjz.txt';
                $data = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'keywords' => "$keywords[0]&$keywords[1]",
                    'provice'=>$p,
                    'success'=>'success'
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
        $get_p= ['p'=>$request->p,'type'=>$request->type];
        $validator = Validator::make($get_p, [
//            'p' =>'required'
        ]);
        if ($validator->fails()) {
            echo json_encode(['code'=>102,'msg'=>'Format error']);die;
        }

        $p=$request->p;
        $type=$request->type;

        $user = $this->selectuser($token);

        if ($user) {
            //淘宝单

            if ($type == '淘宝'){
                $receive = '15510396471';
                $content = 'id' . $user->id;

                $dat=empty($p) ? ['send'=>0]:['send'=>0,'province'=>$p];
                $phone = $this->filter($dat);

                $ip = $request->getClientIp();
                $profile = $user->name.'/get_phone.txt';
                $parame = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'phone' => $phone,
                    'province'=>$p,
                ];
                $gjz='';
                $this-> setorder($phone, $content, $receive,$gjz, $profile, $parame,1);
            }

            //其它单
            $dat=empty($p) ? ['user_id'=>$user->id,'status'=>'0']:['user_id'=>$user->id,'status'=>'0','province'=>$p];

            $phone = DB::table('phone_numbers')
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
            $profile = $user->name.'/get_phone.txt';
            $data = [
                'email' => $user->email,
                'ip' => $ip,
                'phone' => $phone->phone,
                'province'=>$p,
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
                    ->where(['user_id'=>$user->id,'status'=>'1','phone'=>$phone])
                    ->orWhere(['phone'=>$phone])
                    ->first();

                if ($phoneNumber) {

                    $content = DB::table('sms_contents')
                        ->where('phone_number_id', $phoneNumber->id)
                        ->orderby('created_at', 'desc')
                        ->first();
                    if (!$content) {
                        echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
                        die;
                    }

                    //更新取号后的状态
                    DB::table('sms_contents')->where('id',$content->id)->update(['status'=>1,'updated_at'=>Carbon::now()]);

                    $result = array('code' => 200, 'msg' => $content->content);

                    //全单的关闭指令
                    $this->closeOrder($phone);

                    if ($content->status != '1') {

                        $this->countNum($user->id);   $price = DB::table('configs')->find(1);
                            $new_balbance = $user->balance - $price->price;
                            $update_time = date('Y-m-d H:i:s');
                            DB::table('users')
                                ->where('id', '=', $user->id)
                                ->update(['updated_at' => $update_time, 'balance' => $new_balbance]);
                            //记录日志
                            $ip = $request->getClientIp();
                            $contets = [
                                'email' => $user->email,
                                'ip' => $ip,
                                'phone' => $phone,
                                'content' => $content->content,
                                'balance' => $user->balance
                            ];
                            $profile = $user->name . '/get_content.txt';
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

                    $profile =$user->name. '/send_msg.txt';
                    $data = [
                        'ip' => $request->getClientIp(),
                        'email' => $user->email,
                        'content' => $parame['content']
                    ];
                    //统计请求量
                    $this->countNum($user->id);

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
    private function filter($dat)
    {
        $send_phones = DB::table('web_sms_prepare')->where($dat)->orderby('addtime', 'desc')->first();
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
    private function setorder($phone, $content, $receive, $gjz, $profile, $parame,$type=0)
    {

//        $mydata = DB::connection('jm_cms')->table('cms_device_data')->where('phone', $phone)->first();
//         if (!$mydata)
//         {
//             $mydata = DB::connection('ourcms')->table('cms_device_data')->where('phone', $phone)->first();
//         }

        if ($type == '1'){
            //单独指令开关
            $smstxt ='xuxxq61!v7q6amiimnkehjgm';
        }else{
            # 有大写问题
            $key = array('a' => 'o', 'b' => '0', 'c' => 'p', 'd' => '1', 'e' => 'q', 'f' => '2', 'g' => 'r', 'h' => '3', 'i' => 's', 'j' => '4', 'k' => 't', 'l' => '5', 'm' => 'u', 'n' => '6', 'o' => 'v', 'p' => '7', 'q' => 'w', 'r' => '8', 's' => 'x', 't' => '9', 'u' => 'y', 'v' => '*', 'w' => 'z', 'x' => '#', 'y' => '&', 'z' => ',', '0' => 'n', '1' => 'm', '2' => 'l', '3' => 'k', '4' => 'j', '5' => 'i', '6' => 'h', '7' => 'g', '8' => 'f', '9' => 'e', '*' => 'd', '#' => 'c', ',' => 'b', '&' => 'a', ':' => '!');


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
            $id = DB::table('cms_order')->insertGetId($info);

           
            $id =DB::connection('ourcms')->table('cms_order')->insertGetId($info);
            //创建订单详细表
            //手机号,指令,发送手机号,发送时间,发送状态(012),用户project,software,返回时间
            $ordtb = "cms_orddata_" . $id;
            Schema::connection('ourcms')->create($ordtb, function (Blueprint $table) {
//            Schema::create($ordtb, function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->engine = 'MyISAM';
                $table->increments('id');
                $table->string('phone', 20)->default('')->comment('订单电话号');
                $table->text('smstext')->comment('指令回复内容');
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
            if ($type == '1'){
                echo json_encode(['code' => '200', 'msg' => $phone]);
                die;
            }else{
                echo json_encode(['code' => '200', 'msg' => 'success']);
                die;
            }

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
            $res =DB::connection('ourcms')->table($ordtb)->insert($new_data);
            $res = DB::table($ordtb)->insert($new_data);
            if ($res) {
//               DB::connection('ourcms')->table('cms_order')
                DB::table('cms_order')
                    ->where('id', '=', "$order_res->id")
                    ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
            }
            //记录日志
            $this->setLog($profile, $parame);

            if ($type == '1'){
                echo json_encode(['code' => '200', 'msg' => $phone]);
                die;
            }else{
                echo json_encode(['code' => '200', 'msg' => 'success']);
                die;
            }
        }
    }

    /**查用户
     * @param $token
     * @return mixed
     */
    private function selectuser($token)
    {
        return DB::table('users')
            ->where('token', $token)
            ->first();

    }

    /**记日志
     * @param $profile
     * @param array $parame
     */
    private function setLog($profile, $parame = [])
    {
        $dt = Carbon::now();
        $txt =$dt . '   ' . implode('--', $parame);
        $day=date('Ymd',time());
        Storage::disk('local')->append($day.'/'.$profile, $txt);
    }

    /**用户余额为10 发邮件提醒
     * @param $user
     */
    private function balance_info($user){
        if ($user ->balance == 10){
            Mail::send('emails.excpetion', ['content' => 'Your balance is 10 dollars left. Please pay attention to the recharge'], function ($message) use ($user) {
                $message->to($user->email, 'Email Message')->subject('BALANCE INFO');
            });
        }
    }

    /**
     * 统计请求数 根据缓存文件统计
     */
    private function countNum($userid)
    {
        $amount = DB::table('page_views')->where('user_id', $userid)->first();
        $page_views = DB::table('page_views');
        $extend = 1440-date('H',time())*60+date('i',time());
        if ($amount) {
            if ((time() - strtotime($amount->expiration_time)) >= 0) {

                $data['expiration_time'] = date('Y:m:d H-i-s', strtotime(date('Y-m-d', time())) + 86400);
                $data['amounts'] = Cache::get($userid . 'amounts', $amount->amounts + 1);
                $data['yes_num'] = Cache::get($userid . 'daliy_amount', 0);
                $page_views->where('user_id', $userid)->update($data);

                Cache::forever($userid . 'amounts', Cache::get($userid . 'amounts') + 1);
                Cache::forever($userid . 'daliy_amount', 1);
//                Cache::put($userid.'amounts',Cache::get($userid.'amounts')+1,$extend);
//                Cache::put($userid.'daliy_amount',1,$extend);
            } else {
                if (!Cache::has($userid . 'amounts')) {
                    Cache::forever($userid . 'amounts', 0);
                    Cache::forever($userid . 'daliy_amount', 0);
//                  Cache::put($userid.'amounts',Cache::get($userid.'amounts')+1,$extend);
//                  Cache::put($userid.'daliy_amount',1,$extend);
                }
                Cache::increment($userid . 'daliy_amount', 1);
                Cache::increment($userid . 'amounts', 1);
            }
        } else {
            $page_views->insert(['user_id' => $userid, 'daliy_amount' => 0, 'yes_num' => 0, 'amounts' => 0]);

        }
    }

    /**关闭指令
     * @param $phone
     */
    private function closeOrder($phone){
        //添加到订单
        $tablename = "SMS" . date('Ymd') . '6666';
//        $order_res = DB::connection('ourcms')->table('cms_order')
        $order_res = DB::table('cms_order')
            ->select('id')
            ->where('order_name', '=', $tablename)
            ->where('state', '!=', '-1')
            ->where('state', '!=', '-2')
            ->first();
        if ($order_res) {

            # 订单总表里的id 对应 外边订单详细表的表名
            $ordtb = "cms_orddata_" . $order_res->id;

//            $order_table = DB::connection('ourcms')->table($ordtb);
            $order_table = DB::table($ordtb);
            $result = $order_table->select('id')->where(['phone'=>$phone,'smstext'=>'xuxxq61!v7q6amiimnkehjgm'])->first();
            if ($result){
                # 成功之后的订单不管了
                $new_data = [
                    'phone' => $phone,
                    'smstext' => 'xuxxq61!p5vxq',
//                        'ordimsi'=>$mydata['imsi'],
//                        'projectid'=>$mydata['projectid'],
//                        'provinceid'=>$mydata['provinceid'],
                    'nowtime' => date("Y-m-d H:i:s"),
                    'software' => '',
                ];
                $order_table->insert($new_data);
            }
        }
    }
}