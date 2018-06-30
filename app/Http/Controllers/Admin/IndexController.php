<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\PhoneNumber;
use App\Models\SmsContent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Queue\RedisQueue;
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

    /**首页
     * @param Request $request
     * @return
     */
    public function index(Request $request)
    {
        return view('admin.index.index');
    }
    /**余额页面
     * @param Request $request
     * @return
     */
    public function bal(Request $request)
    {
        return view('admin.index.bal');
    }

    public function edit(Request $request)
    {
        if ($request->isMethod('post')){
            //保存数据
            var_dump($request);
        }else{
            $user = new User();
            $user= $user->find($request->id);
            return view('admin.index.edit',compact('user'));
        }

    }

    /**设置单次请求金额
     * @param Request $request
     * @return
     */
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

    /**搜索及分页
     * @param Request $request
     * @return
     */
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
                    $value->amounts      = Cache::get($value->id.'amounts', $value->amounts);

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

    /**设置余额
     * @param Request $request
     * @return
     */
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

    /**根据手机号搜索短信内容
     * @param Request $request
     * @return
     */
    public function search_content(Request $request)
    {
        if ($request->isMethod('post')){
            $post =$request->all();
            $validator = Validator::make($post, [
                'phone' => 'required|regex:/^1[34578][0-9]{9}$/',
            ]);
            if ($validator->fails()) {
                return Y::error($validator->errors());
            }

            $phone= $request->phone;
            $phoneNumber =DB::table('phone_numbers')
                ->select('id')
                ->where('phone',$phone)
                ->first();
            if($phoneNumber) {
                $content = DB::table('sms_contents')
                    ->select('content', 'created_at')
                    ->where('phone_number_id', $phoneNumber->id)
                    ->orderby('created_at', 'desc')
                    ->first();
                if ($content) {
                    if($content->content === 'xuxxq61!p5vxq'){
                        $content = DB::table('sms_contents')
                            ->select('content', 'created_at')
                            ->where('phone_number_id', $phoneNumber->id)
                            ->orderby('created_at', 'asc')
                            ->first();
                    }
                    if ($content) {
                        return $content->created_at . '<br>' . $content->content;
                    }
                } else {
                    return '暂时没有消息';
                }
            }else{
                return '没有这个手机号';
            }

        }else{

        }
   }

    /**显示短信具体的统计数据
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
   public function show_detail(Request $request){

       $user =new User();
       //所有被取走的手机号及省份
      $allIds= $user->getNumber();
       $contents=$user->getSmsNumber();
       $phones = PhoneNumber::select('phone','province','updated_at')->where('status','1')->orderby('updated_at','desc')->limit('10')->get()->toarray();
       $content=SmsContent::select('phone','province','sms_contents.updated_at','content')
           ->leftjoin('phone_numbers','phone_number_id','phone_numbers.id')
           ->where('sms_contents.status','1')->orderby('sms_contents.updated_at','desc')->limit('10')->get()->toarray();
       return view('admin.index.show_detail',compact('allIds','contents','phones','content'));
   }

    public function searchUserContent(Request $request)
    {
        $post =$request->all();
        $validator = Validator::make($post, [
            'username' => 'required',
        ]);

        if ($validator->fails()) {
            return ['code'=>'201','msg'=>'参数错误'];
        }


        $user =new User();
        $id= $user->select('id')->where('name',$post['username'])->first();
        if (!$id){
            return ['code'=>'201','msg'=>'没有这个用户'];
        }
        //号码表所有被取走的手机号及省份
        $allIds= $user->getNumber($id->id);
        $contents=$user->getSmsNumber($id->id);
//
//        $phones = PhoneNumber::select('phone','province','updated_at')->where(['status'=>'1','user_id'=>$id->id])->orderby('updated_at','desc')->limit('10')->get()->toarray();
//        $content=SmsContent::select('phone','province','sms_contents.updated_at','content')
//            ->leftjoin('phone_numbers','phone_number_id','phone_numbers.id')
//            ->where(['sms_contents.status'=>'1','phone_numbers.user_id'=>$id->id])
//
//            ->orderby('sms_contents.updated_at','desc')->limit('10')->get()->toarray();
        return ['allIds'=>$allIds,'contents'=>$contents];

    }
    public function showContents(Request  $request){
        if ($request->isMethod('post')){
            $user_name    = $request->user ;
            $page       = $request->curr ? $request->curr : 1;//当前页
            $num        = $request->nums ? $request->nums : 10;//每页显示的数量


            $user =new  User();
            $userid= $user->select('id')->where('name',$user_name)->first();


//        $rev='每页显示的数量';
            $offset = ($page - 1) * $num;

            if($user_name){
                if (!$userid){
                    return response()->json([
                        'code'  => '',
                        'msg'   => '',
                        'count' => 0,
                        'data'  => []
                    ]);
                }
                $allIds= $user->find($userid->id)->phone()->select('id')->where('status','1')->wherebetween('updated_at',[Carbon::today(),Carbon::tomorrow()])->get()->toarray();
                $nums= count($allIds);
                $ids=[];
                foreach ($allIds as $id){
                    $ids[]=$id['id'];
                }
                $datas = SmsContent::select('phone','province','content','sms_contents.updated_at')
                    ->leftjoin('phone_numbers','phone_number_id','=','phone_numbers.id')
                    ->where('sms_contents.status','1')
                    ->whereIn('phone_number_id',$ids)
                    ->wherebetween('sms_contents.updated_at',[Carbon::today(),Carbon::tomorrow()])
                    ->orderby('updated_at','desc')
                    ->limit($num)
                    ->offset($offset)
                    ->get()
                    ->toArray();

            }else{
                $nums = DB::table('sms_contents')->where('status','1')->wherebetween('updated_at',[Carbon::today(),Carbon::tomorrow()])->count();
                $datas = SmsContent::select('phone','province','content','sms_contents.updated_at')
                    ->leftjoin('phone_numbers','phone_number_id','=','phone_numbers.id')
                    ->where('sms_contents.status','1')
                    ->wherebetween('sms_contents.updated_at',[Carbon::today(),Carbon::tomorrow()])
                    ->orderby('updated_at','desc')
                    ->limit($num)
                    ->offset($offset)
                    ->get()
                    ->toArray();
            }

            return response()->json([
                'code'  => '',
                'msg'   => '',
                'count' => $nums,
                'data'  => $datas
            ]);

        }
          return view('admin.index.showContents');

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
