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
            'token' => 'required',
        ]);
        $token = $request->token;
        $p = $request->get('p');
        $con=empty($request->type)?'京东':$request->type;

        if ($validator->fails()) {
            echo json_encode( ['code' => 102, 'msg' => 'Format error']);die;

        }
        //权限验证
        $user = $this->api->selectuser($token);
        if ($user) {

            $this->api->check_user($user,$p,2);

            //查询配置
            $type=   DB::table('configs')->select('type_id','type_name')->where(['type_name'=>$con,'user_id'=>$user->id])->first();
            if (!$type){
                echo json_encode( ['code' => 105, 'msg' => "Sorry, sir. You have no right to visit"]);die;
            }
            //短信猫
            $receive = $this->api->filter_phones();

            $content = 'id' . $user->id;

            $ip = $request->getClientIp();
            $profile = $user->name . '/key.txt';

            $gjz = '';

            //从prepare取手机号设置关键字
            if ($con=='京东'){
                $dat = empty($p) ? ['send' => 0] : ['send' => 0, 'province' => $p];
            }else{
                $dat = empty($p) ? ['send' => 5] : ['send' => 5, 'province' => $p];
            }

            if ($user->percentum){
                $old_new = strpos($user->percentum, ':') ? explode(":", $user->percentum) : explode("：",$user->percentum);
                $old= $this->api->rand_number($old_new[0],$old_new[1]);
                if ($old ==2){
                    $phone = $this->api->filter($dat,$user->id,$type->type_id,1);
                }else{
                    $phone = $this->api->filter($dat,$user->id,$type->type_id);
                }
            }else{
                $phone = $this->api->filter($dat,$user->id,$type->type_id);
            }

            if (!$phone) {
                echo json_encode(['code' => 107, 'msg' => "No mobile phone number for the time being"]);
                die;
            }

            DB::table('user_filter_phone')->insert(['user_id'=>$user->id,'phone'=>$phone,'type_id'=>$type->type_id,'created_at'=>Carbon::now()]);
            $parame = [
                'email' => $user->email,
                'ip' => $ip,
                'phone' => $phone,
                'province' => $p,
            ];

            //线上的数据库
            $this->api->setorder($phone, $content, $receive, $gjz, $profile, $parame, 1);
            //现在链接是本地的数据库
//            $this->setorder($phone, $content, $receive, $gjz, $profile, $parame, 1);

        } else {
            //无权访问
            echo json_encode(array('code' => 105, 'msg' => "Sorry, sir. You have no right to visit"));
            die;
        }
    }

    /**获取手机号
     * @param Request $request
     */
    public function phone(Request $request)
    {
        $this->api->getPhoneNumber($request,'666666');
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
//            'con'=>'required'
        ]);

        if ($validator->fails()) {
            echo json_encode(['code' => 102, 'msg' => 'Format error']);
            die;
        }
        // 拿手机号的最新短信
        $phone = $request->phone;
        $type =empty($request->con)?'京东':$request->con;

        $user = $this->api->selectuser($token);
        if ($user) {
//查询配置
            $types=   DB::table('configs')->select('type_id','type_name')->where(['type_name'=>$type,'user_id'=>$user->id])->first();
            if (!$types){
                echo json_encode( ['code' => 105, 'msg' => "Sorry, sir. You have no right to visit"]);die;
            }
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


                    $type1='%'.$type.'%码%';
                    $type2='%码%'.$type.'%';
                    //20分钟内
                    $sminute =Carbon::now()->addSeconds(-1200);
                    $content = DB::table('sms_contents')
                        ->select('id', 'content','created_at', 'status','phone_number_id')
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
                        SmsContent::where('id',$content->id)->update(['status'=>'1','updated_at'=>Carbon::now(),'user_id'=>$user->id]);

                    //更新取号后的状态
//                    DB::table('sms_contents')->where('id', $content->id)->update(['status' => '1', 'updated_at' => Carbon::now()]);

                    $result = array('code' => 200, 'msg' => $content->content,'time'=>$content->created_at);

                    if ($content->status != '1') {

                        //根据不同用户不同短信内容更新余额，没有单价的默认按1结算
                        $price=  $this->check_balance($content->content,$user->id);
                        if ($price){
                            $new_balbance = $user->balance - $price;
                        }else{
                            $new_balbance=$user->balance-10;
                        }

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

    /**主动返回短信内容
     * @param Request $request
     */
    public function content(Request $request)
    {
        if ($request->isMethod('post')){

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
            $balance = User::select('name','balance','id')->where('token',$token)->first();
            SmsContent::where('phone_number_id',$phone->id)->limit(1)->update(['user_id'=>$balance->id,'status'=>'1']);

            //根据不同用户不同短信内容更新余额，没有单价的默认按1结算
            $price=  $this->check_balance($content,$balance->id);
            if ($price){
                $new_balbance = $balance->balance - $price;
            }else{
                $new_balbance=$balance->balance-1;
            }

            User::where('token',$token)->update(['balance'=>$new_balbance]);
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

    /**
     * 远程关闭
     */
    public function remote_close()
    {

        if (time() - strtotime(date('Y-m-d'))<=600){
            $yes = date('Ymd')-1;
            $tablename = "SMS" .$yes . '6666';
        }else{
            $tablename = "SMS" . date('Ymd') . '6666';
        }

        $order_res = DB::connection('ourcms')->table('cms_order')
            ->select('id','order_tnum')
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
                ->get()->toarray();

            $n=$m=0;
            $info = array();
            $info['order_tnum'] = $order_res->order_tnum + 1; # 订单 总表 里的订单数据 自增 1
            $info['state'] = 1;//1 open
            $info['addtime'] = time();

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
                    if ($insert) {
                        //更新订单总表数据
                        DB::connection('ourcms')->table('cms_order')
                            ->where('id', '=', "$order_res->id")
                            ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
                        ++$m;
                    }
                }
            }
            $dt= Carbon::now().'调用成功完成,数据表'.$ordtb.'更新'.$n.'条，插入'.$m.'条；';
            Storage::disk('local')->append('cron.txt',$dt);
        }
    }
    //更新日限
    public function update_date_times(Request $request)
    {
//        if ($request->isMethod('post')){
            $name =$request->name;
            if ($name =='byebye2018'){
                Db::table('users')->update(['times'=>0]);
            }
//        }
    }
    //自动分配手机号
    public function auto_allot_phones(Request $request)
    {
        if ($request->eme =='hebezheke2018'){
            $switch =DB::table('all_configs')->where('id',1)->value('switch');
            if ($switch ===0){
                echo 'sorry';
                return;
            }

            //截取或京东
            $type_id=DB::table('type_config')->whereIn('type_name',['京东','截取'])->pluck('id')->toarray();

            $types=   DB::table('configs')->select('user_id','percent')->whereIn('type_id',$type_id)->get()->toarray();
            //日限清零
            foreach ($types as $type){
                $times = User::select('times','date_times')->find($type->user_id);
                if ($times->times > $times->date_times){
                    $type->percent =0;
                }
            }

            $jd= DB::table  ('web_sms_prepare')->where('send',0)->where('type_id','1')->pluck('id')->toarray();
            $num =count($jd);
            if ($num <100){return;}

            $txt=Carbon::now()."总共条{$num}：";
            foreach ($types as $type){

                $limt=  round($num*($type->percent)/100 );
                $res=  DB::table('web_sms_prepare')->whereIn('id',$jd)->where('user_id','0')->where('type_id','1')->limit($limt)->update(['user_id'=>$type->user_id,'type_id'=>'-1']);
                $txt .= 'id为'.$type->user_id.'--'.$res.'条,';
            };
            Storage::disk('local')->append('fenpei.txt',$txt);
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

    //更新余额
    private function find_type($content='')
    {
        $types= DB::table('type_config')->get()->toarray();

        foreach ($types as $type){
          $res=  @str_contains($content, $type->type_name);
            if ($res){
                return $type->id;
            }
        }
    }
    //查询单价
    public function check_balance($content,$user_id)
    {
        $type_id=$this->find_type($content);

        $user_price=   DB::table('configs')->where('user_id',$user_id)->where('type_id',$type_id)->value('price');
        if (!$user_price){
            $user_price= DB::table('configs')->where('user_id',0)->where('type_id',$type_id)->value('price');
        }
        return $user_price;
    }
}
