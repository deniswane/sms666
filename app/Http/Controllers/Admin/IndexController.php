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
                ->select('name','email','balance','updated_at','created_at')
                ->limit($num)
                ->where('name',$user_name)
                ->offset($offset)
                ->get()
                ->toArray();
        }else{
            $nums = DB::table('users')->count();
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
    //清空缓存
    public function flush()
    {
        Cache::flush();

        return response()->json(['code'=>200,'msg'=>'成功']);
    }

}
