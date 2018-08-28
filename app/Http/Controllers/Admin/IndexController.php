<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\PhoneNumber;
use App\Models\SmsContent;
use App\Models\User;
use Carbon\Carbon;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Library\Y;
use PayPal\Api\Phone;

class IndexController extends Controller
{
    public function __construct()
    {

        $this->middleware('admin', [
            'except' => ['show', 'create', 'store']
        ]);

    }

    public function ceshi()
    {
//        echo date('Ymd' , strtotime("-3 day")).'235959';


    }

    /**首页
     * @param Request $request
     * @return
     */
    public function index(Request $request)
    {

        return view('cfcc.index.index');
    }

    /**余额页面
     * @param Request $request
     * @return
     */
    public function bal(Request $request)
    {
        return view('cfcc.index.bal');
    }

    public function edit(Request $request)
    {
        if ($request->isMethod('post')) {
            //保存数据
            var_dump($request);
        } else {
            $user = new User();
            $user = $user->find($request->id);
            return view('cfcc.index.edit', compact('user'));
        }

    }

    /**设置单次请求金额
     * @param Request $request
     * @return
     */
    public function set_money(Request $request)

    {

        if ($request->isMethod('post')) {

            $page = $request->curr ? $request->curr : 1;//当前页
            $num = $request->nums ? $request->nums : 10;//每页显示的数量
            $offset = ($page - 1) * $num;

            $nums=DB::table('configs')->count();
         $datas= DB::table('configs')
             ->select('users.name','configs.id','configs.type_name','price','configs.user_id')
             ->leftjoin('users','users.id','=','configs.user_id')->orderby('configs.user_id')
             ->limit($num)->offset($offset)
             ->get()->toarray();
            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $datas,
            ]);

        }  else if($request->isMethod('get')) {
            $price = DB::table('configs')->select('price')->find(1);
            return view('cfcc.index.set_money', ['price' => $price])->__toString();
        }

    }

    /**添加类别
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function set_money_add(Request $request)
    {
        if ($request->isMethod('post')){
            $type_name=$request->type_name;
            try {
                $res=DB::table('type_config')->insertGetId(['type_name'=>$type_name]);
                if ($res){
                    return ['code' => 200, 'msg' => '添加成功！'];
                }

            } catch (\Exception $e) {
                return ['code' => 202, 'msg' => '添加失败，请检查输入的是否有重复！'];
            }

        }
        $types =DB::table('type_config')->pluck('type_name');
        return view('cfcc.index.set_money_add',compact('types'));
    }

    /**添加用户类别
     * @param Request $request
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function set_money_user_add(Request $request)
    {
        if ($request->isMethod('post')){
            $type=$request->type;
            $uesr =$request->user;
            $price =$request->price;
            $type_name=DB::table('type_config')->where('id',$type)->value('type_name');

            try {

                $res=  DB::table('configs')->insertGetId(['type_name'=>$type_name,'type_id'=>$type,'price'=>$price,'user_id'=>$uesr]);
            } catch (\Exception $e) {
                return ['code' => 202, 'msg' => '请检查是否已经存在'];
            }

            if ($res){
                return ['code'=>200,'msg'=>'添加成功'];
            }else{
                return ['code'=>201,'添加失败'];
            }
        }
        $users=User::select('name','id','email')->get();
        $types =DB::table('type_config')->select('id','type_name')->get();
        return view('cfcc.index.set_money_user_add',compact('types','users'));
}

    /**删除用户类别
     * @param Request $request
     * @return array
     */
    public function set_money_user_delete(Request $request)
    {
        if ($request->isMethod('post')){
            $id=$request->id;
           $res= DB::table('configs')->where('id',$id)->delete();
           if ($res){
               return ['code'=>200,'msg'=>'删除成功'];
           }else{
               return ['code'=>201,'msg'=>'删除失败'];
           }
        }
    }

    /**更新单价
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function set_price(Request $request)
    {
        if ($request->isMethod('post'))
        $post = $request->all();
        $validator = Validator::make($post, [
            'price' => 'numeric|required',
        ]);
        if ($validator->fails()) {
            return Y::error($validator->errors());
        }
        $price=$request->price;
        $id=$request->id;
       $res= DB::table('configs')->where('id',$id)->update(['price'=>$price]);
        if ($res){
            return ['code'=>200,'msg'=>'更新成功'];
        }
    }
    /**搜索及分页
     * @param Request $request
     * @return
     */
    public function test(Request $request)
    {
        $page = $request->curr ? $request->curr : 1;//当前页
        $num = $request->nums ? $request->nums : 10;//每页显示的数量

//        $rev='每页显示的数量';
        $offset = ($page - 1) * $num;

        $nums = User::count();
        $to_datas = User::select('name', 'email','sms_contents.content')
//        $to_datas = User::select(DB::raw('min(sms_contents.id),ANY_VALUE(name) as name,ANY_VALUE(email) as email,ANY_VALUE(sms_contents.content) as content'))
            ->leftjoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftjoin('sms_contents', 'phone_numbers.id', '=', 'sms_contents.phone_number_id')
            -> whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()])
            ->where(function($query){
                $query->where('sms_contents.status', '1')
                ->orWhere(function($qua){
                    $qua->where(['sms_contents.status' => '0'])
                    ->where('tb_st', '1')
                    ->where('jd_st', '!=', '1');
                })
                ->orWhere(function($qua){
                    $qua->where(['sms_contents.status' => '0'])
                    ->where('jd_st', '1')
                    ->where('tb_st', '!=', '1');
                });;

            })
            ->get()
            ->toArray();
        $abc = [];
        foreach ($to_datas as $data) {
            if (isset($data['content'])) {
                $abc[$data['email']][] = $data['content'];
            } else {
                $abc[$data['email']] = [];
            }
        }
            $ye_datas = User::select('name', 'email','sms_contents.content')
            ->leftjoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftjoin('sms_contents', 'phone_numbers.id', '=', 'sms_contents.phone_number_id')
                -> whereBetween('sms_contents.updated_at', [Carbon::yesterday(), Carbon::today()])
                ->where(function($query){
                    $query->where('sms_contents.status', '1')
                        ->orWhere(function($qua){
                            $qua->where(['sms_contents.status' => '0'])
                                ->where('tb_st', '1')
                                ->where('jd_st', '!=', '1');
                        })
                        ->orWhere(function($qua){
                            $qua->where(['sms_contents.status' => '0'])
                                ->where('jd_st', '1')
                                ->where('tb_st', '!=', '1');
                        });
                })
            ->get()
            ->toArray();
        $abcd = [];
        foreach ($ye_datas as $data) {
            if (isset($data['content'])) {
                $abcd[$data['email']][] = $data['content'];
            } else {
                $abcd[$data['email']] = [];
            }
        }
        $datas = User::select('email', 'name', 'balance', 'created_at','switch','date_times','percentum')
            ->limit($num)->offset($offset)->get()->toarray();
        foreach ($datas as &$user) {
            if (isset($abc[$user['email']])) {
                $user['daliy_amount'] = count($abc[$user['email']]);

            } else {
                $user['daliy_amount'] = 0;
            }
            if (isset($abcd[$user['email']])) {
                $user['yes_num'] = count($abcd[$user['email']]);

            } else {
                $user['yes_num'] = 0;
            }

        }

        return response()->json([
            'code' => '',
            'msg' => '',
            'count' => $nums,
            'data' => $datas,
            'abc' => $abc
        ]);

    }

    /**设置余额
     * @param Request $request
     * @return
     */
    public function set_bal(Request $request)
    {
        $post = $request->all();
        $validator = Validator::make($post, [
            'balance' => 'numeric|required',
        ]);
        if ($validator->fails()) {
            return Y::error($validator->errors());
        }
        if ($post['balance']) {
            $res = DB::table('users')->where('email', '=', $post['email'])->update(['balance' => $post['balance']]);
            if ($res) {
                $name = Auth::guard('admin')->user()->name;//
                $ip = $request->getClientIp();
                $dt = Carbon::now() . ' 管理员' . $name . ' ip是 ' . $ip . '给' . $post['email'] . '设置了金额' . $post['balance'];
                Storage::disk('local')->append('set_balance.txt', $dt);
                return ['code' => '200'];
            }
        }
    }

    /**根据手机号搜索短信内容
     * @param Request $request
     * @return
     */
    public function search_content(Request $request)
    {
        if ($request->isMethod('post')) {
            $post = $request->all();
            $validator = Validator::make($post, [
                'phone' => 'required|regex:/^1[34578][0-9]{9}$/',
            ]);
            if ($validator->fails()) {
                return Y::error($validator->errors());
            }

            $phone = $request->phone;
            $phoneNumber = DB::table('phone_numbers')
                ->select('id')
                ->where('phone', $phone)
                ->first();
            if ($phoneNumber) {
                $contents = SmsContent::select('sms_contents.updated_at', 'content')->leftjoin('phone_numbers', 'phone_numbers.id', '=', 'sms_contents.phone_number_id')
                    ->where('phone', $phone)->orderby('sms_contents.updated_at', 'desc')->get()->toarray();;

                if ($contents) {
                    $str = '';
                    foreach ($contents as $content) {
                        $str .= $content['updated_at'] . '<br>' . $content['content'] . '<br>';
                    }
                    return $str;

                } else {
                    return '暂时没有消息';
                }
            } else {
                return '没有这个手机号';
            }

        } else {

        }
    }

    /**显示短信具体的统计数据
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show_detail(Request $request)
    {
        $user = new User();
        //所有被取走的手机号及省份
        $allIds = $user->getNumber();
        $contents = $user->getSmsNumber();
        $phones = DB::table('filter_phone')->select('code', 'status')->get();
        return view('cfcc.index.show_detail', compact('allIds', 'contents', 'phones'));
    }

    //搜索
    public function searchUserContent(Request $request)
    {
        $post = $request->all();
        $validator = Validator::make($post, [
            'username' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ['code' => '201', 'msg' => '参数错误'];
        }


        $user = new User();
        $id = $user->select('id')->where('email', $post['username'])->first();
        if (!$id) {
            return ['code' => '201', 'msg' => '没有这个用户'];
        }
        //号码表所有被取走的手机号及省份
        $allIds = $user->getNumber($id->id);
        $contents = $user->getSmsNumber($id->id);
//        dd($allIds,$contents);
        return ['allIds' => $allIds, 'contents' => $contents];

    }

    /**显示短信内容
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function showContents(Request $request)
    {
        if ($request->isMethod('post')) {
            $email = $request->user;
            $page = $request->curr ? $request->curr : 1;//当前页
            $num = $request->nums ? $request->nums : 10;//每页显示的数量


            $user = new  User();
            $userid = $user->select('id')->where('email', $email)->first();


//        $rev='每页显示的数量';
            $offset = ($page - 1) * $num;

            if ($email) {
                if (!$userid) {
                    return response()->json([
                        'code' => '',
                        'msg' => '',
                        'count' => 0,
                        'data' => []
                    ]);
                }

                $contents = SmsContent::select('phone', 'province', 'content', 'sms_contents.updated_at')
                    ->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
                    ->where(['sms_contents.status' => '1', 'user_id' => $userid->id])
                    ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()])
                    ->orWhere(function ($query) use ($userid) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where(['user_id' => $userid->id])
                            ->where('tb_st', '1')
                            ->where('jd_st', '!=', '1')
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    })
                    ->orWhere(function ($query) use ($userid) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where(['user_id' => $userid->id])
                            ->where('jd_st', '1')
                            ->where('tb_st', '!=', '1')
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    });
                $nums = $contents->count();
                $datas = $contents->orderby('sms_contents.updated_at', 'desc')
                    ->limit($num)
                    ->offset($offset)
//                    ->toSql();
//                dd($datas);
                    ->get()
                    ->toArray();

            } else {
                $nums = DB::table('sms_contents')
                    ->wherebetween('updated_at', [Carbon::today(), Carbon::tomorrow()])
                    ->where(function ($query) {
                        $query->where('sms_contents.status', '1')
                            ->orWhere(function ($quer) {
                                $quer->where('sms_contents.status', '0')
                                    ->where('tb_st', '1')
                                    ->where('jd_st', '!=', '1');
                            })
                            ->orWhere(function ($quer) {
                                $quer->where('sms_contents.status', '0')
                                    ->where('jd_st', '1')
                                    ->where('tb_st', '!=', '1');
                            });
                    })
                    ->count();;
//
                $datas = SmsContent::select('phone', 'province', 'content', 'sms_contents.updated_at')
                    ->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
                    ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()])
                    ->where(function ($query) {
                        $query->where('sms_contents.status', '1')
                            ->orWhere(function ($quer) {
                                $quer->where('sms_contents.status', '0')
                                    ->where('tb_st', '1')
                                    ->where('jd_st', '!=', '1');
                            })
                            ->orWhere(function ($quer) {
                                $quer->where('sms_contents.status', '0')
                                    ->where('jd_st', '1')
                                    ->where('tb_st', '!=', '1');
                            });
                    })
//
                    ->orderby('sms_contents.updated_at', 'desc')
                    ->limit($num)
                    ->offset($offset)
                    ->get()
                    ->toArray();
            }
//                dd ($datas);

            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $datas
            ]);

        }
        return view('cfcc.index.showContents');

    }

    /**总体数据统计
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function month_detail(Request $request)
    {
        if ($request->isMethod('post')) {
            $start = $request->start;
            $end = $request->end;
            $pro = $request->province;
            $user = $request->userid;

            $contents = SmsContent::leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
                ->select(DB::raw('count(*) as total,province'));
            $countphones = PhoneNumber::select(DB::raw('count(*) as number,province'));
            if ($start) {
                $contents = $contents->where('sms_contents.updated_at', '>=', $start);
                $countphones = $countphones->where('created_at', '>=', $start);

            }
            if ($end) {

                $contents = $contents->where('sms_contents.updated_at', '<=', $end);
                $countphones = $countphones->where('created_at', '<=', $end);

            }
            if ($pro) {
                $contents = $contents->where('phone_numbers.province', '=', $pro);
                $countphones = $countphones->where('province', '=', $pro);
            }
            if ($user) {
                $contents = $contents->where('phone_numbers.user_id', '=', $user);
                $countphones = $countphones->where('user_id', '=', $user);
            }
            $provinces = $contents->
            where(function ($query) {
                $query->where('sms_contents.status', '1')
                    ->orWhere(function ($quer) {
                        $quer->where('jd_st', '1')
                            ->where('tb_st', '!=', '1')
                            ->where('sms_contents.status', '0');
                    })
                    ->orWhere(function ($quer) {
                        $quer->where('tb_st', '1')
                            ->where('jd_st', '!=', '1')
                            ->where('sms_contents.status', '0');
                    });
            })
                ->groupby('province')->get()->toarray();
//            dd($provinces);
            $phones = $countphones->where('status', '1')->groupby('province')->get()->toarray();

            $datas = $provinces;

            foreach ($provinces as $key => $province) {
                foreach ($phones as $phone) {
                    if ($province['province'] === $phone['province']) {
                        $datas[$key]['number'] = $phone['number'];

                    } else {
                        unset($phone);

                    }
                }
            }
            $nums = PhoneNumber::select('province')
                ->groupby('province')
                ->get()->count();

            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $datas,
                'contenr' => $provinces
            ]);
        } else {

            $provinces = PhoneNumber::select('province')->groupby('province')->get()->toarray();
            $user = User::select('id', 'name')->get()->toarray();
            return view('cfcc.index.month_detail', compact('provinces', 'user'));
//            return view('cfcc.index.month_detail');
        }
    }

    /**今日筛选数据统计
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function to_filter_detail()
    {
        $start = date('YmdHis', strtotime(Carbon::today()));
        $end = date('YmdHis', strtotime(Carbon::tomorrow()));
        $c = $this->filter_detail($start, $end);
        return view('cfcc.index.filter_detail', compact('c'));

    }

    /**昨日筛选数据统计
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ye_filter_detail()
    {
        $start = date('YmdHis', strtotime(Carbon::yesterday()));
        $end = date('YmdHis', strtotime(Carbon::today()));
        $c = $this->filter_detail($start, $end);
        return view('cfcc.index.filter_detail', compact('c'));
    }

    //所有的筛选数据统计
    public function all_filter_detail()
    {
        $c = $this->filter_detail();
        return view('cfcc.index.filter_detail', compact('c'));
    }

    /**数据统计
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filter_detail($start = '', $end = '')
    {
        if (!empty($start)) {
            $all_phone = array_map('get_object_vars', DB::table('web_sms_prepare')
                ->select(DB::raw('count(*) as total,province'))->where('send', '5')
                ->whereBetween('addtime', [$start, $end])->orWhere('send', '0')->whereBetween('addtime', [$start, $end])->groupby('province')->get()->toarray());
            $send_phone = array_map('get_object_vars', DB::table('web_sms_prepare')->select(DB::raw('count(*) as nun,province'))->where('send', '5')->whereBetween('addtime', [$start, $end])->groupby('province')->get()->toarray());

        } else {
            $all_phone = array_map('get_object_vars', DB::table('web_sms_prepare')->select(DB::raw('count(*) as total,province'))->where('send', '5')->orWhere('send', '0')->groupby('province')->get()->toarray());
            $send_phone = array_map('get_object_vars', DB::table('web_sms_prepare')->select(DB::raw('count(*) as nun,province'))->where('send', '5')->groupby('province')->get()->toarray());
        }

        $c = array();
        foreach ($all_phone as $e) $c[$e['province']] = $e;
        foreach ($send_phone as $e) $c[$e['province']] = isset($c[$e['province']]) ? $c[$e['province']] + $e : $e;

        return $c;
    }

    /**短信表有内容后请求成功第三方的数据
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function all_return_detail(Request $request)
    {
        if ($request->isMethod('post')) {
            $start = $request->start;
            $end = $request->end;
            $type = $request->type;
            $page = $request->curr ? $request->curr : 1;//当前页
            $num = $request->nums ? $request->nums : 10;//每页显示的数量
//        $rev='每页显示的数量';
            $offset = ($page - 1) * $num;

            $datas = DB::table('callback_result')->select('phone', 'created_at', 'type', 'result');
//            $datas = DB::table('callback_result')->select(DB::raw('ANY_VALUE(min(id)) as id,ANY_VALUE(phone) as phone,ANY_VALUE(created_at) as create_at,ANY_VALUE(type) as type,ANY_VALUE(result) as result'));

            if ($start) {
                $datas = $datas->where('created_at', '>=', $start);
            }
            if ($end) {
                if ($end == $start) {
                    $end = Carbon::tomorrow($start);
                }
                $datas = $datas->where('created_at', '<=', $end);
            }
            if (isset($type)) {
                $datas = $datas->where('type', '=', "$type");
            }
            $datas = $datas->orderby('created_at', 'desc');
//            $datas = $datas->orderby(DB::raw('ANY_VALUE(created_at)'), 'desc')->groupby('phone', 'type');
            $nums = $datas->count();
            $success_rate = $datas->get()->toarray();
            $results = $datas->limit($num)->offset($offset)->get()->toarray();

//二维数组 京东 淘宝 成功失败
            $jd_success = $tb_success = $jd_fail = $tb_fail = 0;
            foreach ($success_rate as $result) {
                if ($result->type == '0') $result->result == '成功' || $result->result == '' ? $jd_success += 1 : $jd_fail += 1;
                if ($result->type == '1') $result->result == '成功' || $result->result == '' ? $tb_success += 1 : $tb_fail += 1;

            }

            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $results,
                'type' => $type,
                'result' => ['jd_success' => $jd_success, 'jd_fail' => $jd_fail, 'tb_success' => $tb_success, 'tb_fail' => $tb_fail]
            ]);

        } else {
            return view('cfcc.index.all_return_detail');
        }
    }

    public function filter_phone(Request $request)
    {
        if ($request->isMethod('post')) {
            $phones = DB::table('filter_phone')->get();
            $nums = DB::table('filter_phone')->count();
            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $phones,
            ]);
        } elseif ($request->isMethod('put')) {
            $code = $request->code;
            $status = $request->status;
            $res = DB::table('filter_phone')->where('code', $code)->update(['code' => $code, 'status' => $status]);
            if ($res) {
                return ['code' => 200, 'msg' => 'success'];
            } else {
                return ['code' => 202, 'msg' => 'fail'];
            }
        } else {
            return view('cfcc.index.filter_phone');
        }
    }

    public function filter_phone_add(Request $request)
    {
        $phone = $request->phone;
        $code = $request->code;
        $key = array('a' => 'o', 'b' => '0', 'c' => 'p', 'd' => '1', 'e' => 'q', 'f' => '2', 'g' => 'r', 'h' => '3', 'i' => 's', 'j' => '4', 'k' => 't', 'l' => '5', 'm' => 'u', 'n' => '6', 'o' => 'v', 'p' => '7', 'q' => 'w', 'r' => '8', 's' => 'x', 't' => '9', 'u' => 'y', 'v' => '*', 'w' => 'z', 'x' => '#', 'y' => '&', 'z' => ',', '0' => 'n', '1' => 'm', '2' => 'l', '3' => 'k', '4' => 'j', '5' => 'i', '6' => 'h', '7' => 'g', '8' => 'f', '9' => 'e', '*' => 'd', '#' => 'c', ',' => 'b', '&' => 'a', ':' => '!');
        //单独指令开关
        $_smstext = 'smssend:open&' .$phone;
        $smstxt = '';
        for ($i = 0; $i < strlen($_smstext); $i++) {
            $smstxt .= $key[$_smstext[$i]];   # 转换为 ‘密文’
        }

        if ($request->isMethod('post')) {
            try {
                $res = DB::table('filter_phone')->insert(['code' => $code, 'phone' => $phone,'order'=>$smstxt]);
                if ($res){
                    return ['code' => 200, 'msg' => '添加成功！'];
                }
            } catch (\Exception $e) {
                return ['code' => 202, 'msg' => '添加失败，请检查输入的是否有重复！'];
            }
        }elseif ($request->isMethod('put')){
            try {
                $res = DB::table('filter_phone')->where(function($query) use($phone,$code){
                    $query->where('phone',$phone)
                        ->orWhere('code',$code);
                })->update(['phone'=>$phone,'code'=>$code,'order'=>$smstxt]);
                    return ['code' => 200, 'msg' => '更新成功！'];

            } catch (\Exception $e) {
                return ['code' => 202, 'msg' => '请更新1个字段并确定与其的编号或者手机号不重复！'];
            }
        } else {
            if ($code){
               $res= DB::table('filter_phone')->where('code',$code)->delete();
                if ($res){
                    return ['code' => 200, 'msg' => '删除成功！'];
                }else{
                    return ['code' => 202, 'msg' => '删除失败！'];

                }
            }
            return view('cfcc.index.filter_phone_add');
        }
    }


    public function change_switch(Request $request)
    {
        if ($request->isMethod('post')){
            $met=$request->met ;
            $code =$request->code;

            switch ($met){
                case 'switch' :
                    $status=$request->status;
                    $res= User::where('email',$code)->update(['switch'=>$status]);
                    if ($res){
                        return ['code'=>200,'msg'=>'更新成功'];
                    }else{
                        return ['code'=>201,'msg'=>'更新失败'];
                    }
                    break;
                case 'date_times':
                    $status=$request->date_times;
                    $res= User::where('email',$code)->update(['date_times'=>$status]);
                    if ($res){
                        return ['code'=>200,'msg'=>'更新成功'];
                    }else{
                        return ['code'=>201,'msg'=>'更新失败'];
                    }
                    break;
                case 'set_rate':
                    $percentum=$request->percentum;
                    if (!empty($percentum)){
                        if( !str_contains($percentum,[':','：'])  ){
                            return ['code'=>202,'msg'=>'请检查参数'];
                        }
                    }

                   $res =User::where('email',$code)->update(['percentum'=>$percentum]);
                    if ($res){
                        return ['code'=>200,'msg'=>'更新成功'];
                    }else{
                        return ['code'=>201,'msg'=>'更新失败'];
                    }
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
