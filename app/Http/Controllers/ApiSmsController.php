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

class ApiSmsController extends Controller
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
        $p = $request->p;
        if ($validator->fails()) {
            echo json_encode( ['code' => 102, 'msg' => 'Format error']);die;

        }
        //权限验证
        $user = $this->api->selectuser($token);
        if ($user) {
            if ($user->balance <= 0) {
               echo json_encode(['code' => 106, 'msg' => 'You need to charge money']);die;
            }
            //根据token 判断是京东还是淘宝
            switch ($token){
                case $this->jd_token: //京东的用户token
                     $type_sta = 'jd_st';
                break;
                case  $this->tb_token://淘宝用户的token
                     $type_sta = 'tb_st';
                break;
                default:
                    //没有用户对应的类型（京东、淘宝） 返回错误
                 echo json_encode(['code' => 202, 'msg' => 'Please contact the staff']);die;
                break;
            }
            //根据不同的用户去短信表 判断获取手机号
            if ($type_sta){
                //取手机号
                //1.从sms_contents表链表 phone_numbers取当天最新的做判断
                $province = empty($p) ?'':$p;
                $phones = SmsContent::select('sms_contents.id','phone')->leftjoin('phone_numbers','phone_number_id','=','phone_numbers.id');
                if ($province){
                    $phone = $phones->where('province',$province);
                }
                $phone = $phones->where($type_sta,'0')->orderby('sms_contents.updated_at','desc')->first();
             //开关 开

                $receive = '15510396471';
                $content = 'id' . $user->id;


                $ip = $request->getClientIp();
                $profile = $user->name . '/get_phone.txt';
                $parame = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'phone' => $phone,
                    'province' => $p,
                ];
                $gjz = '';
                //开关

                if ($phone){
                    if ($phone->phone){
                        SmsContent::where('id',$phone->id)->update([$type_sta=>'2']);
                        //打开开关的
//                        $this->setorder($phone->phone, $content, $receive, $gjz, $profile, $parame, 1);
                        $this->api->setorder($phone, $content, $receive,$gjz, $profile, $parame,1);

//                      return ['code' => '200', 'msg' => $phone->phone];
                    }
                }


                //2.从prepare取新的手机号
                $dat = empty($p) ? ['send' => 0] : ['send' => 0, 'province' => $p];
                $phone = $this->api->filter($dat);
                if (!$phone){
                    echo  json_encode(['code' => 107, 'msg' => "No mobile phone number for the time being"]);die;

                }
                //线上的数据库
               $this->api->setorder($phone, $content, $receive,$gjz, $profile, $parame,1);
                //现在链接是本地的数据库
//                $this->setorder($phone, $content, $receive, $gjz, $profile, $parame, 1);
            }else{
                //没有用户对应的类型（京东、淘宝） 返回错误
                echo json_encode(array('code' => '202', 'msg' => 'Please contact the staff.'));
                die;
            }

        } else {
            echo  json_encode( ['code' => 105, 'msg' => "Sorry, sir. You have no right to visit"]);die;
        }

    }

    public function content(Request $request)
    {
        $dt = Carbon::now();
        $txt =$dt . '   ' . implode('--', $request->all());
        Storage::disk('local')->append('ceshi.txt', $txt);
        echo json_encode($request->all());
        echo '66666';die;
        //接收红良的请求
        $type = $request->type;
        $tel = $request->phone;
        $content = $request->con;


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

        //更新手机号表的手机状态为
        $phone = PhoneNumber::select('phone','status','id')->where('phone',$tel)->first();
      if ($phone){
          if ($phone->status =='0'){
              PhoneNumber::where('id',$phone->id)->update(['status'=>'1']);
          }
          //更新余额
          $balance = User::select('balance')->where('token',$token)->first();
          $price = DB::table('configs')->select('price')->find(1);
          $new_balbance = $balance->balance - $price->price;
          User::where('token',$token)->update(['balance'=>$new_balbance]);


          $url='http://mt.cdwashcar.com/index/sp/content?token='.$value.'&tel='.$tel.'&content='.$content;

          $result = json_decode($this->curl_request($url),true);

          $type = $token==$this->jd_token?'0':'1';

          $val=[
              'phone'=>$tel,
              'type'=>$type,
              'created_at'=>Carbon::now(),
          ];

            $val['result']=$result['msg'];

        //记录文本
          $profile = '/result.txt';
          $parame = [
              'phone' => $tel,
              'type' =>$type,
              'result'=>$result['msg']
          ];
        $this->api->setLog($profile,$parame);
        DB::table('callback_result')->insert($val);
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
             return  Cache::put($name.'token',$result['data']['token'],119);
           }
       }

    }



    public function setorder($phone, $content, $receive, $gjz, $profile, $parame, $type = 0)
    {

//        $mydata = DB::connection('jm_cms')->table('cms_device_data')->where('phone', $phone)->first();
//         if (!$mydata)
//         {
//             $mydata = DB::connection('ourcms')->table('cms_device_data')->where('phone', $phone)->first();
//         }

        if ($type == '1') {
            //单独指令开关
            $smstxt = 'xuxxq61!v7q6amiimnkehjgm';
        } else {
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
        $order_res = DB::table('cms_order')
            ->select('id', 'order_tnum')
            ->where('order_name', '=', $tablename)
            ->where('state', '!=', '-1')
            ->where('state', '!=', '-2')
            ->first();
//        dd($order_res);
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
            //创建订单详细表
            //手机号,指令,发送手机号,发送时间,发送状态(012),用户project,software,返回时间
            $ordtb = "cms_orddata_" . $id;
            Schema::create($ordtb, function (Blueprint $table) {
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
            DB::table($ordtb)->insert($new_data);

            //记录日志
            $this->api->setLog($profile, $parame);
            if ($type == '1'){
                echo json_encode(['code' => 200, 'msg' => $phone]);
                die;
            }else{
                echo json_encode(['code' => 200, 'msg' => 'success']);
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
            $res = DB::table($ordtb)->insert($new_data);
            if ($res) {
                DB::table('cms_order')
                    ->where('id', '=', "$order_res->id")
                    ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
            }
            //记录日志
            $this->api->setLog($profile, $parame);

            if ($type == '1'){
                echo json_encode(['code' => 200, 'msg' => $phone]);
                die;
            }else{
                echo json_encode(['code' => 200, 'msg' => 'success']);
                die;
            }
        }
    }

    public function remote_close(Request $request){
        //添加到订单
//        if (!$request ->isMethod('post')) return ;
//        $token = $request->token;
//        if ($token != '666666') return ;
        $tablename = "SMS" . date('Ymd') . '6666';
        $order_res = DB::connection('ourcms')->table('cms_order')
//        $order_res = DB::table('cms_order')
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
//            $order_table = DB::table($ordtb);
            $overdue = date("Y-m-d H:i:s",time()-180);//三分钟
            if(date('his',time())>'235945'){
                $opens = $order_table->select('phone','id')
                    ->where(['return_times'=>'0','smstext'=>'xuxxq61!v7q6amiimnkehjgm']);
            }else{
                $opens = $order_table->select('phone','id')
                    ->where(['return_times'=>'0','smstext'=>'xuxxq61!v7q6amiimnkehjgm'])
                    ->where('nowtime','<=',$overdue)->get()->toarray();
            }



            //批量更新
//            DB::update(DB::raw("UPDATE sms_contents SET tb_st = 1,jd_st=1 WHERE jd_st = 0 "));
            $n=$m=0;

            if ($opens){

                foreach ($opens as $key =>$open){
                    $new_data = [
                        'phone' =>$open->phone ,
                        'smstext' => 'xuxxq61!p5vxq',
////                        'ordimsi'=>$mydata['imsi'],
////                        'projectid'=>$mydata['projectid'],
////                        'provinceid'=>$mydata['provinceid'],
                        'nowtime' => date("Y-m-d H:i:s"),
                        'software' => '',
                    ];
                    $update=DB::table($ordtb) ->where('id', $open->id)->update(['return_times'=>'1']);
                    if($update) ++$n;
                    $insert= DB::table($ordtb)->insert($new_data);
                    if ($insert) ++$m;
                }
            }
//
        }
        echo '调用成功完成,数据表'.$ordtb.'更新'.$n.'条，插入'.$m.'条；';

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
}
