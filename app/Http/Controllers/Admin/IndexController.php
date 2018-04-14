<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Library\Y;

class IndexController extends Controller
{
    public function __construct() {

        $this->middleware('admin', [
            'except' => ['show', 'create', 'store']
        ]);

    }
    //首页控制台
    public function index(Request $request)
    {
        return view('admin.index.bal');
    }
    public function bal(Request $request)
    {
        return view('admin.index.bal');
    }
    //设置钱
    public function set_money(Request $request)

    {

        if ($request->isMethod('post')) {
            $post = $request->post();
            $validator = Validator::make($post, [
                'prices' => 'numeric|required',
            ]);

            if ($validator->fails()) {
                return Y::error($validator->errors());
            }
            $data=[
                'price' =>$post['prices'],
                'updated_at' =>date('Y-m-d H:i:s',time()),
            ]   ;

            if (Admin\Config::where('id', '1')->update($data) > 0) {
                return Y::success('修改成功');

            }
            return Y::error('修改失败');
        }  else {
            $price =  DB::table('configs')->find(1);
            return view('admin.index.set_money',['price'=>$price]);
        }

    }
    //ajax分页及搜索
    public function test(Request $request)
    {

        $user_name    = $request->user ;
        $page       = $request->curr ? $request->curr : 1;//当前页
        $num        = $request->nums ? $request->nums : 10;//每页显示的数量


//        $rev='每页显示的数量';
        $offset = ($page - 1) * $num;

        if($user_name){

            $nums = DB::table('users')->where('name',$user_name)->count();
            $datas = DB::table('users')
                ->select('name','email','balance','updated_at','created_at')
                ->limit($num)
                ->where('name',$user_name)
                ->offset($offset)
                ->get()
                ->toArray();
        }else{
            $nums = DB::table('users')->count();
//
//            $datas = DB::table('bals')
//                ->select('phone_numbers.phone','users.name','bals.id','bals.num','bals.updated_at','bals.created_at')
//                ->leftJoin('phone_numbers', 'bals.phone_id', '=', 'phone_numbers.id')
//                ->leftJoin('users', 'bals.user_id', '=', 'users.id')
//
//                ->limit($num)
//                ->offset($offset)
//                ->get()
//                ->toArray();
            $datas = DB::table('users')
                ->select('name','email','balance','updated_at','created_at')
                ->limit($num)
                ->offset($offset)
                ->get()
                ->toArray();
        }

        return response()->json([
            'code' => '',
            'msg' => '',
            'count' => $nums,
            'data' => $datas
        ]);

    }
    //点击手机号返回最新的消息
    public function phone_info(Request $request)
    {
        $phone = $request->phone;
        if($phone){
            $phone_id=(Db::table('phone_numbers')->where('phone',$phone)->value('id'))*1;
            $info   = DB::table('sms_contents')->select('content')->where('phone_number_id',$phone_id)->orderBy('updated_at','desc')->limit(1)->first();
            if($info){
                return $info['content'];
            }else{
                return "暂时没有信息";
            }
        }else{
            return "暂时没有信息";
        }
    }
    //清空缓存
    public function flush()
    {
        Cache::flush();

        return response()->json(['code'=>200,'msg'=>'成功']);
    }
//今天到上个月的今天 没用
    private function getdays()
    {
        $day= $this->last_month_today(time());//上个月的今天
        $month = date('m'); //取当前月份
        $month = $month==1 ? $month = 12: $month-1;

        $year = date('Y');
        $daynum = cal_days_in_month(CAL_GREGORIAN, $month, $year); //根据当月的年月份值，得到该月的天数
        $days = date("d");
        $arr=array();
        for($j=$day;$j<=$daynum;$j++){
            if(date('m') == 1){
                $arr[]="12-".$j;
            }else{
                $arr[]=(date("m")-1)."-".$j;
            }

        }
        for($i=1;$i<=$days;$i++){
            $arr[]=date("m")."-".$i;
        }

        var_dump($arr);
    }

    private   function last_month_today($time){
        $last_month_time = mktime(date("G", $time), date("i", $time),
            date("s", $time), date("n", $time), 0, date("Y", $time));
        $last_month_t =  date("t", $last_month_time);
        $day = $last_month_t < date("j", $time) ? date("t", $last_month_time) : date( "d", $time);
        return $day;

    }
}
