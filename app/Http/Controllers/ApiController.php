<?php

namespace App\Http\Controllers;
header("Content-type: text/html; charset=utf-8");

use App\Models\PhoneNumber;
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
    private  $online;

    public function __construct()
    {
        $this->online = new SmsOnlineController();
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
            //短信猫
            $receive = $this->filter_phones();

            if ($user) {


                $this->check_user($user,1);

                //有权限访问 返回的标记
                $content = 'id' . $user->id;
                $dat = empty($p) ? ['send' => 0] : ['send' => 0, 'province' => $p];

                if ($user->percentum){
                    $old_new = strpos($user->percentum, ':') ? explode(":", $user->percentum) : explode("：",$user->percentum);
                    $old= $this->rand_number($old_new[0],$old_new[1]);
                    if ($old ==2){
                        $phone = $this->filter($dat,$user->id,1);
                    }else{
                        $phone = $this->filter($dat,$user->id);
                    }
                }else{
                    $phone = $this->filter($dat,$user->id);
                }

                if (!$phone) {
                    //没有手机号，发联网订单
                    $this->online->online_phone($user,$keywords,$receive,$p);
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
                    'phone'=>$phone,
                    'provice' => $p,
                    'success' => 'success'
                ];
                //直接返回手机号
                if  ($user->email=='godaddy1210@gmail.com'){
                    $this->setorder($phone, $content, $receive, $gjz, $profile, $data,2);
                }else{
                    $this->setorder($phone, $content, $receive, $gjz, $profile, $data);

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

           $this->check_user($user);

            $dat = empty($p) ? ['user_id' => $user->id, 'status' => '0'] : ['user_id' => $user->id, 'status' => '0', 'province' => $p];

            $phone = DB::table('phone_numbers')
                ->select('phone', 'id')
                ->where($dat)
                ->orderby('created_at', 'desc')
                ->first();
            if (!$phone) {
                //日志
                $ip = $request->getClientIp();
                $profile = $user->name . '/get_phone.txt';
                $data = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'phone' => '没有手机号返回',
                    'province' => $p,
                ];
                $this->setLog($profile, $data);
                echo json_encode(['code' => '107', 'msg' => 'No mobile phone number for the time being']);
                die;
            }

            DB::table('users')->where('id',$user->id)->increment('times', 1);
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

                    $content= DB::table('sms_contents')
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
                    $result = array('code' => 200, 'msg' => $content->content);

                    if ($content->status != '1') {
                        //截取的每次减1
                        $new_balbance = $user->balance - 1;
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
    public function filter($dat,$id,$old='')
    {
        if ($old){
            $start_time=Carbon::today()->modify('-3 days');
            $end_time=Carbon::today()->modify('-2 days');
            $send_phones =PhoneNumber::select('phone','phone_numbers.id')->leftjoin('sms_contents','phone_numbers.id','=','sms_contents.phone_number_id')
                ->where('user_id',$id)
                ->where('phone_numbers.created_at','>',$start_time)
                ->where('phone_numbers.created_at','<',$end_time)
                ->where('phone_numbers.status','<>','2')
                ->whereNull('sms_contents.phone_number_id')
                ->first();
            if ($send_phones){
                PhoneNumber::where('id',$send_phones->id)->update(['status'=>'2']);

                return $send_phones->phone;

            }else{
                $send_phones = DB::table('web_sms_prepare')->select('phone', 'id')->where($dat)->orderby('addtime', 'desc')->first();
                if (!$send_phones) {
                    return false;
                }
                DB::table('web_sms_prepare')->where('id', $send_phones->id)->update(['send' => 5]);
                return $send_phones->phone;
            }
        }
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
//        $order_res = DB::connection('ourcms')->table('cms_order')
        $order_res = DB::table('cms_order')
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
            $id = DB::table('cms_order')->insertGetId($info);
//            $id = DB::connection('ourcms')->table('cms_order')->insertGetId($info);
            //创建订单详细表
            //手机号,指令,发送手机号,发送时间,发送状态(012),用户project,software,返回时间
            $ordtb = "cms_orddata_" . $id;
//            Schema::connection('ourcms')->create($ordtb, function (Blueprint $table) {
            Schema::create($ordtb, function (Blueprint $table) {
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
//            DB::connection('ourcms')->table($ordtb)->insert($new_data);
            DB::table($ordtb)->insert($new_data);

            //记录日志
            $this->setLog($profile, $parame);
            if($type ==2 ){
                echo json_encode(['code' => '200', 'phone' => $phone]);
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
//            $res = DB::connection('ourcms')->table($ordtb)->insert($new_data);
            $res = DB::table($ordtb)->insert($new_data);
            if ($res) {
//                DB::connection('ourcms')->table('cms_order')
                DB::table('cms_order')
                    ->where('id', '=', "$order_res->id")
                    ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
            }
            //记录日志
            $this->setLog($profile, $parame);

            if($type ==2 ){
                echo json_encode(['code' => '200', 'phone' => $phone]);
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
    public function selectuser($token)
    {

        return DB::table('users')
            ->select('token', 'id', 'email', 'balance', 'name', 'times','switch','date_times','percentum')
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

    /**短信猫
     * @return mixed
     */
    public function filter_phones()
    {
        $filter_phones= DB::table('filter_phone')->select('phone')->where('status','1')->get()->toarray();
        $phones=[];
        foreach ($filter_phones as $phone){
            $phones[]=$phone->phone;
        }
        return $phones[array_rand($phones)];
    }

    /**检查用户
     * @param $user
     * @param int $type
     */
    public function check_user($user,$type=1)
    {
        if ($user->switch == 0){
            if (empty($p)){
                echo json_encode(['code' => 102, 'msg' => 'Format error']);
                die;
            }
        }
        if ($user->times >= $user->date_times){
            echo json_encode(['code' => 107, 'msg' => 'No mobile phone number for the time being']);
            die;
        }
        if ($user->balance <= 0) {
            echo json_encode(array('code' => 106, 'msg' => 'You need to charge money'));
            die;
        }
        if ($user->id == 17 || $user->id ==18){
            $tim='time'.$user->id;
            if (!Cache::has($tim)){
                Cache::put($tim,time(),1);
            };
            $extim = time()- Cache::get($tim);
            if ($extim <1){
                if ($type==1){
                    echo json_encode(['code' => 107, 'msg' => 'No mobile phone number for the time being']);
                    die;
                }else{
                    echo json_encode(['code' => 200, 'msg' => 'success']);
                    die;
                }

            }else{
                Cache::put($tim,time(),1);
            }
        }
    }

    /**新旧数据比例
     * @param $new
     * @param $old
     * @return mixed
     */
    public function rand_number($new,$old)
    {
        $prize_arr = array(
            '0' => array('id'=>1,'v'=>$new),
            '1' => array('id'=>2,'v'=>$old),
        );

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v']; //将$prize_arr放入数组下标为$prize_arr的id元素，值为v元素的数组中
        }

        $rid = $this->get_rand($arr); //根据概率获取奖项id

        $res['phone'] = $prize_arr[$rid-1]['id']; //获取中奖项

        unset($prize_arr[$rid-1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for($i=0;$i<count($prize_arr);$i++){
            $pr[] = $prize_arr[$i];
        }
         return $res['phone'];
    }
    private function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr); //计算数组中元素的和

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) { //如果这个随机数小于等于数组中的一个元素，则返回数组的下标
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }

        unset ($proArr);

        return $result;
    }

}