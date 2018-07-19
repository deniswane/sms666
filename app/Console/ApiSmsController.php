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

//                $receive = '15510396471';
//                $receive = '15932011375';
                $receives=['13061195162','15932011375'];
                $receive=$receives[array_rand($receives)];

                $content = 'id' . $user->id;


                $ip = $request->getClientIp();
                $profile = $user->name . '/phone.txt';

                $gjz = '';
                //开关

                if ($phone){
                    if ($phone->phone){
                        SmsContent::where('id',$phone->id)->update([$type_sta=>'2']);
                        //打开开关的
                $parame = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'phone' => $phone->phone,
                    'province' => $p,
                    'type_sta'=>$type_sta
                ];
//                        $this->setorder($phone->phone, $content, $receive, $gjz, $profile, $parame, 1);
                        $this->api->setorder($phone->phone, $content, $receive,$gjz, $profile, $parame,1);

//                      return ['code' => '200', 'msg' => $phone->phone];
                    }
                }


                //2.从prepare取新的手机号
                $dat = empty($p) ? ['send' => 0] : ['send' => 0, 'province' => $p];
                $phone = $this->api->filter($dat);
                if (!$phone){
                    echo  json_encode(['code' => 107, 'msg' => "No mobile phone number for the time being"]);die;

                }

                $parame = [
                    'email' => $user->email,
                    'ip' => $ip,
                    'phone' => $phone,
                    'province' => $p,
                    'type_sta'=>$type_sta
                ];

                //线上的数据库
               $this->api->setorder($phone, $content, $receive,$gjz, $profile, $parame,1);
                //现在链接是本地的数据库
//                $this->setorder($phone, $content, $receive, $gjz, $profile, $parame, 1);
            }else{
                //没有用户对应的类型（京东、淘宝） 返回错误
                echo json_encode(array('code' => 202, 'msg' => 'Please contact the staff.'));
                die;
            }

        } else {
            echo  json_encode( ['code' => 105, 'msg' => "Sorry, sir. You have no right to visit"]);die;
        }

    }

    public function content(Request $request)
    {

        //接收红良的请求
        $type = $request->type;
        $tel = $request->phone;
        $content = $request->con;
       if( !$this->check_type($content))return;
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

        //更新手机号表的手机状态为
        $phone = PhoneNumber::select('phone','status','id')->where('phone',$tel)->first();
      if ($phone){
          if ($phone->status =='0'){
              PhoneNumber::where('id',$phone->id)->update(['status'=>'1']);
          }
          //更新余额
          $balance = User::select('balance','id')->where('token',$token)->first();
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

        //记录文本
          $profile = '/result.txt';
          $parame = [
              'phone' => $tel,
              'type' =>$type,
              'result'=>$result['msg']
          ];
        $this->api->setLog($profile,$parame);
      }





    }
private function check_type($content){
    if(strpos($content,'京东') !==false){
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
                        'nowtime' => date("Y-m-d H:i:s"),
                        'software' => '',
                    ];
                    $update=$order_table ->where('id', $open->id)->update(['return_times'=>'1']);
                    if($update) ++$n;
                    $insert=$order_table->insert($new_data);
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