<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\User;
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
            if(isset($post['prices'])){
                $validator = Validator::make($post, [
                    'prices' => 'numeric|required',
                ]);

                if ($validator->fails()) {
                    return Y::error($validator->errors());
                }
                $data=[
                    'price' =>$post['prices'],
                    'created_at' =>date('Y-m-d H:i:s',time()),
                ]   ;

            }else{
                $validator = Validator::make($post, [
                    'price_min' => 'numeric|required',
                    'price_max' => 'numeric|required',
                    'num_min' => 'numeric|required',
                    'num_max' => 'numeric|required',
                ]);

                if ($validator->fails()) {
                    return Y::error($validator->errors());
                }
                $data=[
                    'price_i' =>$post['price_min'],
                    'price_a' =>$post['price_max'],
                    'num_a' =>$post['num_max'],
                    'num_i' =>$post['num_min'],
                    'num_updated_at' =>date('Y-m-d H:i:s',time()),
                ];
            }
            if (Admin\Config::where('id', '1')->update($data) > 0) {

                return Y::success('修改成功');
            }
            return Y::error('修改失败');
        }  else {
            $price =  DB::table('configs')->select('price','price_i','price_a','num_a','num_i')->find(1);
            return view('admin.index.set_money',['price'=>$price])->__toString();
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
                ->select('name','users.id','email','balance','expiration_time','yes_num','daliy_amount','amounts','updated_at','created_at')
                ->leftjoin('page_views','users.id','=','page_views.user_id')
                ->limit($num)
                ->where('name',$user_name)
                ->offset($offset)
                ->get()
                ->toArray();
        }else{
            $nums = DB::table('users')->count();
            $datas = DB::table('users')
                ->select('name','users.id','email','balance','daliy_amount','yes_num','expiration_time','amounts','updated_at','created_at')
                ->leftjoin('page_views','users.id','=','page_views.user_id')
                ->limit($num)
                ->offset($offset)
                ->get()
                ->toArray();
        }

        foreach ($datas as $value){
            //统计数据显示
            if ($value->expiration_time){
                //根据数据表内的过期时间判断
                if( time()-strtotime( $value -> expiration_time ) >= 0 ){
                    $value->daliy_amount = 0;
                    $value->yes_num      = Cache::get($value->id.'daliy_amount', 0) ;
                }else{
                    $value->daliy_amount = Cache::get($value->id.'daliy_amount', $value->daliy_amount);
                    $value->amounts      = Cache::get($value->id.'amounts', $value->amounts);
                }
                if( time()-strtotime( $value -> expiration_time ) >= 86400) {
                    $value->yes_num      = 0;
                }
            }else{
                $value->yes_num          = 0;
                $value->daliy_amount     = 0;
                $value->amounts          = 0;
                }

        }

        return response()->json([
            'code'  => '',
            'msg'   => '',
            'count' => $nums,
            'data'  => $datas
        ]);

    }
    //设置余额
    public function set_bal(Request $request)
    {
        $post =$request->all();
        $validator = Validator::make($post, [
            'balance' => 'numeric|required',
        ]);
        if ($validator->fails()) {
            return Y::error($validator->errors());
        }
         if($post['balance']){
            $res= DB::table('users')->where('email','=',$post['email'])->update(['balance'=>$post['balance']]);
             if ($res){
                 return ['code'=>'200'];
             }
         }
   }
    //清空缓存
//    public function flush()
//    {
//        $ids=User::all()->pluck('id');
//        foreach ($ids as $value){
//            if(Cache::has($value.'daliy_amount')){
//                DB::table('page_views')
//                    ->where(['user_id'=>$value])
//                    ->update(['daliy_amount'=> Cache::get($value.'daliy_amount'),'amounts'=>Cache::get($value.'amounts')])
//                ;
//            }
//        }
//
//        Cache::flush();
//
//        return response()->json(['code'=>200,'msg'=>'成功']);
//    }

}
