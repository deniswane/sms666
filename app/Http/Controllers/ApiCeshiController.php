<?php

namespace App\Http\Controllers;

use App\Models\PhoneNumber;
use App\Models\SmsContent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

class ApiCeshiController extends Controller
{
    private $api;
    private $jd_token = '0e38aa2a1e7493bae75d500944aaf75e';
    private $tb_token= '3a7f2857fe43dad65ab98f54d4de6bc6';
    public function __construct()
    {
        $this->api = new ApiController();
    }

    /**取手机号
     * @param Request $request
     * @return array
     */
    public function key(Request $request)
    {
        //数据验证
        $parames = $request->all();
        $validator = Validator::make($parames, [
            'token' => 'required'
        ]);
        $token = $request->token;
        $p = $request->get('p');
        if ($validator->fails()) {
            echo json_encode( ['code' => 102, 'msg' => 'Format error']);die;

        }
        //权限验证
        $user = $this->api->selectuser($token);
        if ($user) {

            if ($user->balance <= 0) {
                echo json_encode(['code' => 106, 'msg' => 'You need to charge money']);
                die;
            }

            //取手机号

            $receive = $this->api->filter_phones();

            $content = 'id' . $user->id;

            $ip = $request->getClientIp();
            $profile = $user->name . '/key.txt';

            $gjz = '';

            //从prepare取手机号设置关键字

            $dat = empty($p) ? ['send' => 0] : ['send' => 0, 'province' => $p];

            $phone = $this->api->filter($dat);
            if (!$phone) {
                echo json_encode(['code' => 107, 'msg' => "No mobile phone number for the time being"]);
                die;
            }
            DB::table('user_filter_phone')->insert(['user_id'=>$user->id,'phone'=>$phone,'created_at'=>Carbon::now()]);
            $parame = [
                'email' => $user->email,
                'ip' => $ip,
                'phone' => $phone,
                'province' => $p,
            ];

            //线上的数据库
//            $this->api->setorder($phone, $content, $receive, $gjz, $profile, $parame, 1);
            //现在链接是本地的数据库
                $this->setorder($phone, $content, $receive, $gjz, $profile, $parame, 1);

        } else {
            //无权访问
            echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));
            die;
        }
    }

    public function phone(Request $request)
    {
        $this->api->getPhoneNumber($request);
    }

    /**获取短信内容
     * @param Request $request
     */
    public function getSmsContent(Request $request)
    {
        // 验证数据
        $token = $request->token;

        $get_phone = ['phone' => $request->phone,'con'=>$request->con];


        $validator = Validator::make($get_phone, [
            'phone' => 'required |regex:/^1[34578][0-9]{9}$/',
            'con'=>'required'
        ]);

        if ($validator->fails()) {
            echo json_encode(['code' => 102, 'msg' => 'Format error']);
            die;
        }
        // 验证token(对应账号有没有钱)
        // 拿手机号的最新短信
        $phone = $request->phone;
        $user = $this->api->selectuser($token);
        if ($user) {

            if ($user->balance > 0) {
                $phoneNumber = DB::table('phone_numbers')
                    ->select('id')
                    ->where(['user_id' => $user->id, 'status' => '1', 'phone' => $phone])
                    ->orWhere(['phone' => $phone])
                    ->first();

                if ($phoneNumber) {
                    //获取短信内容
                    // 1.截取短信 status=0,jd_st=1,tb_st=1;
                    //2.京东或淘宝短信 status=0,jd_st=1,tb_st=0或status=0,jd_st=0,tb_st=1
                    //3.其它短信，排除垃圾短信   状态 status=0,jd_st=1,tb_st=1;
                    $type =$request->con;
                    $type1='%'.$type.'%码%';
                    $type2='%码%'.$type.'%';
                    //20分钟内
                    $sminute =Carbon::now()->addSeconds(-1200);
                    $content = DB::table('sms_contents')
                        ->select('id', 'content', 'status','phone_number_id')
                        ->where('phone_number_id', $phoneNumber->id)
                        ->where('created_at','>',$sminute)

                        ->where(function ($query)use($type1,$type2){
                            $query->where('content','like',$type1)
                                ->orWhere('content','like',$type2);
                        })
                        ->orderby('created_at', 'desc')

                        ->first();

                    if (!$content) {
                        echo json_encode(array('code' => 401, 'msg' => 'No new text messages'));
                        die;
                    }
                    //取走号后查询有没有注册过另一个京东或者淘宝的，如果都注册过把另一个更新为2
                    if ($type=='京东'){
                       $tb= SmsContent::select('id')->where('phone_number_id',$content->phone_number_id)->where('content','like','%淘宝%')->get()->toarray();
                        if ($tb){

                            foreach ($tb as $t){
                               SmsContent::where('id',$t['id'])->update(['jd_st'=>'2']);
                                SmsContent::where('id',$content->id)->update(['tb_st'=>'2','status'=>'1','updated_at'=>Carbon::now()]);
                            }
                        }else{
                            SmsContent::where('id',$content->id)->update(['status'=>'1','updated_at'=>Carbon::now()]);

                        }
                    }elseif ($type=='淘宝'){
                        $jd= SmsContent::select('id')->where('phone_number_id',$content->phone_number_id)->where('content','like','%京东%')->get()->toarray();

                        if ($jd){
                            foreach ($jd as $t){
                                 SmsContent::where('id',$t['id'])->update(['tb_st'=>'2']);
                                    SmsContent::where('id',$content->id)->update(['jd_st'=>'2','status'=>'1','updated_at'=>Carbon::now()]);
                            }
                        } else{
                            SmsContent::where('id',$content->id)->update(['status'=>'1','updated_at'=>Carbon::now()]);
                        }
                    }else{
                        SmsContent::where('id',$content->id)->update(['status'=>'1','updated_at'=>Carbon::now()]);

                    }

                    //更新取号后的状态
//                    DB::table('sms_contents')->where('id', $content->id)->update(['status' => '1', 'updated_at' => Carbon::now()]);

                    $result = array('code' => 200, 'msg' => $content->content);

                    if ($content->status != '1') {
                        $price = DB::table('configs')->select('price')->find(1);
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
                        $profile = $user->name . '/'.date('Ymd',time()).'.txt';
                        $this->api->setLog($profile, $contets);
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


    /**主动发送
     * @param Request $request
     */
    public function content(Request $request)
    {

        //接收红良的请求
        $type = $request->type;
        $tel = $request->phone;
        $content = $request->con;
        if( !$this->check_type($content)) {echo $content.'--'.$tel.'--'.$type;die;}

        echo "123456";

        switch ($type){
            case '京东':
                $value=  $this->gettoken('jd_sms');
                $this->change_balance($this->jd_token,$tel,$content,$value);
                break;
            case '淘宝':

                $value=$this->gettoken('tb_sms');
                $this->change_balance($this->tb_token,$tel,$content,$value);
                break;
        };

    }

    private function change_balance($token,$tel,$content,$value)
    {

        $res= DB::table('callback_result')->select('id')->where('phone',$tel)->where('created_at','>',Carbon::today())->first();
        if ($res) return;
        //更新手机号表的手机状态为
        $phone = PhoneNumber::select('phone','status','id')->where('phone',$tel)->first();
        if ($phone){
            if ($phone->status =='0'){
                PhoneNumber::where('id',$phone->id)->update(['status'=>'1']);
            }
            //更新余额
            $balance = User::select('name','balance','id')->where('token',$token)->first();
            $price = DB::table('configs')->select('price')->find(1);
            $new_balbance = $balance->balance - $price->price;
            User::where('token',$token)->update(['balance'=>$new_balbance]);
            PhoneNumber::where('id',$phone->id)->update(['user_id'=>$balance->id]);

            $url='http://mt.cdwashcar.com/index/sp/content?token='.$value.'&tel='.$tel.'&content='.$content;

            $result = json_decode($this->curl_request($url),true);

            $type = $token==$this->jd_token?'0':'1';

            if ($result['code']=='0'){
                $result['msg']='成功';
            }

            $val=[
                'phone'=>$tel,
                'type'=>$type,
                'created_at'=>Carbon::now(),
            ];

            $val['result']=$result['msg'];

            DB::table('callback_result')->insert($val);
            $type = $type=='0'?'京东':'淘宝';

            //记录文本
            $profile = $balance->name.'/'.date('Ymd',time()).'.txt';
            $parame = [
                'phone' => $tel,
                'type' =>$type,
                'result'=>$result['msg']
            ];
            $this->api->setLog($profile,$parame);
        }
    }
    private function check_type($content){
        if(strpos($content,'京东') !==false ){
            return true;
        }elseif(strpos($content,'淘宝') !==false){
            return true;
        }else{
            return false;
        }
    }
    /**获取对方登陆的token
     * @param $name
     */
    private function gettoken($name){

        if( Cache::has($name.'token')){
            return Cache::get($name.'token');
        }else{
            $url='http://mt.cdwashcar.com/index/sp/login?user='.$name.'&pass=123456';
            $result=json_decode($this->curl_request($url),true);
            if ($result['code']==0){
                Cache::put($name.'token',$result['data']['token'],119);
                return Cache::get($name.'token');
            }
        }

    }


    public function remote_close()
    {
        if (time() - strtotime(date('Y-m-d'))<=600){
            $yes = date('Ymd')-1;
            $tablename = "SMS" .$yes . '6666';
        }else{
            $tablename = "SMS" . date('Ymd') . '6666';
        }

        $order_res = DB::connection('ourcms')->table('cms_order')
            ->select('id')
            ->where('order_name', '=', $tablename)
            ->where('state', '!=', '-1')
            ->where('state', '!=', '-2')
            ->orderby('addtime','desc')
            ->first();
        if ($order_res) {
            # 订单总表里的id 对应 外边订单详细表的表名
            $ordtb = "cms_orddata_" . $order_res->id;
            $order_table = DB::connection('ourcms')->table($ordtb);
            $overdue = date("Y-m-d H:i:s",time()-600);//十分钟

            # 有大写问题
            //单独指令开关
            $order = DB::table('filter_phone')->pluck('order');
            $opens = $order_table->select('phone','id')
                ->where('return_times','0')
                ->where('nowtime','<=',$overdue)
                ->whereIn('smstext',$order)
//                ->where(function ($query){
//                    $query->where('smstext','=','xuxxq61!v7q6amieklnmmkgi')
//                        ->orWhere('smstext','=','xuxxq61!v7q6amknhmmeimhl')
//                        ->orWhere('smstext','=','xuxxq61!v7q6amklkikhjlln')
//                        ->orWhere('smstext','=','xuxxq61!v7q6amihinnejmni')
//                        ->orWhere('smstext','=','xuxxq61!v7q6amkllnhnhfgm');
//                })

                ->get()->toarray();

            $n=$m=0;
            //单独指令开关
            if ($opens){
                foreach ($opens as $key =>$open){
                    $new_data = [
                        'phone' =>$open->phone ,
                        'smstext' => 'xuxxq61!p5vxq',
                        'nowtime' => date("Y-m-d H:i:s"),
                        'software' => '',
                    ];
                    $update= DB::connection('ourcms')->table($ordtb) ->where('id', $open->id)->update(['return_times'=>'1']);
                    if($update) ++$n;
                    $insert=DB::connection('ourcms')->table($ordtb)->insert($new_data);
                    if ($insert) ++$m;
                }
            }
            $dt= Carbon::now().'调用成功完成,数据表'.$ordtb.'更新'.$n.'条，插入'.$m.'条；';
            Storage::disk('local')->append('cron.txt',$dt);
        }
    }
    /**curl请求
     * @param $url
     * @param bool $post
     * @param array $data
     * @param bool $https
     * @return mixed
     */
    private function curl_request($url,$post=false,$data=array(),$https=false){
        // 使用curl_setopt函数配置curl请求（设置请求方式请求参数）
        //使用curl_init函数初始化curl请求（设置请求地址）
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect'=>'')); //头部要送出'Expect: '

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPV4协议解析域名

        //判断post请求
        if($post){
            //设置请求方式
            curl_setopt($ch,CURLOPT_POST,true);
            //设置请求参数
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        //https协议请求默认发送http协议请求，如果HTTPS请求
        if($https){
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);//检测ssl证书 这里是进制检测
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        }
        //使用curl_exec函数发送curl请求
        //默认返回true|false 若果需要获取请求的执行结果，需要设置CURLOPT__RETURNTRANSFER
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $result=curl_exec($ch);
        //请求结束使用curl_close函数关闭curl请求，释放资源
        curl_close($ch);
        //返回结果给调用方
        return $result;
    }

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
            $this->api->setLog($profile, $parame);
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
            $this->api->setLog($profile, $parame);

            if($type ==2 ){
                echo json_encode(['code' => '200', 'phone' => $phone]);
                die;
            }else{
                echo json_encode(['code' => '200', 'msg' => 'success']);
                die;
            }

        }
    }


}
