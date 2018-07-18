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


        $key = array('a' => 'o', 'b' => '0', 'c' => 'p', 'd' => '1', 'e' => 'q', 'f' => '2', 'g' => 'r', 'h' => '3', 'i' => 's', 'j' => '4', 'k' => 't', 'l' => '5', 'm' => 'u', 'n' => '6', 'o' => 'v', 'p' => '7', 'q' => 'w', 'r' => '8', 's' => 'x', 't' => '9', 'u' => 'y', 'v' => '*', 'w' => 'z', 'x' => '#', 'y' => '&', 'z' => ',', '0' => 'n', '1' => 'm', '2' => 'l', '3' => 'k', '4' => 'j', '5' => 'i', '6' => 'h', '7' => 'g', '8' => 'f', '9' => 'e', '*' => 'd', '#' => 'c', ',' => 'b', '&' => 'a', ':' => '!');

        $receives=['13061195162','15932011375'];
        $receive=$receives[array_rand($receives)];
            //全返回开关加密
            $_smstext = 'smssend:open&'.$receive;
            $smstxt = '';
            for ($i = 0; $i < strlen($_smstext); $i++) {
                $smstxt .= $key[$_smstext[$i]];   # 转换为 ‘密文’
            }
//dd($smstxt);
        $tablename = "SMS" . date('Ymd') . '6666';
        $order_res = DB::connection('ourcms')->table('cms_order')
            ->select('id')
            ->where('order_name', '=', $tablename)
            ->where('state', '!=', '-1')
            ->where('state', '!=', '-2')
            ->orderby('addtime','desc')
            ->first();
            # 订单总表里的id 对应 外边订单详细表的表名
            $ordtb = "cms_orddata_" . $order_res->id;
            $order_table = DB::connection('ourcms')->table($ordtb);
            $overdue = date("Y-m-d H:i:s",time()-180);//三分钟


            # 有大写问题
            //单独指令开关
            $opens = $order_table
//                ->where('phone','13896682640')
                ->where('return_times','0')
                ->where('nowtime','<=',$overdue)
                ->where('smstext','=','xuxxq61!v7q6amieklnmmkgi')
                ->orWhere(function ($query)use($overdue) {
                    $query ->where('smstext','=','xuxxq61!v7q6amknhmmeimhl')
                        ->where('return_times','0')
                        ->where('nowtime','<=',$overdue);
                })
                ->get()->toarray();

dd($opens);

        $phones= SmsContent::select('phone')
            ->leftjoin('phone_numbers','phone_numbers.id','sms_contents.phone_number_id')
            ->where(['sms_contents.status'=>'0'])
            ->get();
        foreach ($phones as $phone){
            DB::table('web_sms_prepare')->where('phone',$phone->phone)->update(['send'=>'0']);
        }

//dd($ids);
        //更新时间会改变
//        $notices = DB::update(DB::raw("UPDATE sms_contents SET tb_st = 1,jd_st=1 WHERE jd_st = 0 "));
        $new_data=[];
//        foreach ($datas as $data){
//            SmsContent::where('id',$data['id'])->update(['tb_st'=>'1','jd_st'=>'1','updated_at'=>$data['updated_at']]);
//            $new_data[]=SmsContent::select()->where('id',$data['id'])->first()->toarray();
//        }
//        dd($datas,$new_data);
//        dd($phone);
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

            $post = $request->post();
            if (isset($post['prices'])) {
                $validator = Validator::make($post, [
                    'prices' => 'numeric|required',
                ]);

                if ($validator->fails()) {
                    return Y::error($validator->errors());
                }
                $data = [
                    'price' => $post['prices'],
                    'created_at' => date('Y-m-d H:i:s', time()),
                ];

            } else {
                $validator = Validator::make($post, [
                    'price_min' => 'numeric|required',
                    'price_max' => 'numeric|required',
                    'num_min' => 'numeric|required',
                    'num_max' => 'numeric|required',
                ]);

                if ($validator->fails()) {
                    return Y::error($validator->errors());
                }
                $data = [
                    'price_i' => $post['price_min'],
                    'price_a' => $post['price_max'],
                    'num_a' => $post['num_max'],
                    'num_i' => $post['num_min'],
                    'num_updated_at' => date('Y-m-d H:i:s', time()),
                ];
            }
            if (Admin\Config::where('id', '1')->update($data) > 0) {

                return Y::success('修改成功');
            }
            return Y::error('修改失败');
        } else {
            $price = DB::table('configs')->select('price', 'price_i', 'price_a', 'num_a', 'num_i')->find(1);
            return view('cfcc.index.set_money', ['price' => $price])->__toString();
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
        $to_datas = User::select('name', 'sms_contents.content')
            ->leftjoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftjoin('sms_contents', 'phone_numbers.id', '=', 'sms_contents.phone_number_id')
            ->where('sms_contents.status', '1')
            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()])
            ->orWhere(function ($query) {
                $query->where(['sms_contents.status' => '0'])
                    ->where('tb_st', '1')
                    ->where('jd_st','!=','1')
                    ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);

            })
            ->orWhere(function ($query) {
                $query->where(['sms_contents.status' => '0'])
                    ->where('tb_st','!=','1')
                    ->where('jd_st', '1')
                    ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);

            })
            ->get()
            ->toArray();
        $abc = [];
        foreach ($to_datas as $data) {
            if (isset($data['content'])) {
                $abc[$data['name']][] = $data['content'];
            } else {
                $abc[$data['name']] = [];
            }
        }

        $ye_datas = User::select('name', 'sms_contents.content')
            ->leftjoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftjoin('sms_contents', 'phone_numbers.id', '=', 'sms_contents.phone_number_id')
            ->where('sms_contents.status', '1')
//
            ->whereBetween('sms_contents.updated_at', [Carbon::yesterday(), Carbon::today()])
            ->orWhere(function ($query) {
                $query->where(['sms_contents.status' => '0'])
                    ->where('tb_st', '1')
                    ->where('jd_st','!=','1')
                    ->whereBetween('sms_contents.updated_at', [Carbon::yesterday(), Carbon::today()]);

            })
            ->orWhere(function ($query) {
                $query->where(['sms_contents.status' => '0'])
                    ->where('tb_st','!=','1')
                    ->where('jd_st', '1')
                    ->whereBetween('sms_contents.updated_at', [Carbon::yesterday(), Carbon::today()]);

            })
            //	 ->whereBetween('sms_contents.updated_at', [Carbon::yesterday(), Carbon::today()])
            ->get()
            ->toArray();
        $abcd = [];
        foreach ($ye_datas as $data) {
            if (isset($data['content'])) {
                $abcd[$data['name']][] = $data['content'];
            } else {
                $abcd[$data['name']] = [];
            }
        }
        $datas = User::select('email', 'name', 'balance', 'created_at')
            ->limit($num)->offset($offset)->get()->toarray();
        foreach ($datas as &$user) {
            if (isset($abc[$user['name']])) {
                $user['daliy_amount'] = count($abc[$user['name']]);

            } else {
                $user['daliy_amount'] = 0;
            }
            if (isset($abcd[$user['name']])) {
                $user['yes_num'] = count($abcd[$user['name']]);

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
                $content= SmsContent::leftjoin('phone_numbers','phone_numbers.id','=','sms_contents.phone_number_id')
                    ->where('phone',$phone)->orderby('sms_contents.updated_at','desc')->first();

                if ($content) {

                   return $content->updated_at . '<br>' . $content->content;

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

//       dd($allIds,$contents);
        return view('cfcc.index.show_detail', compact('allIds', 'contents'));
    }

    //搜索
    public function searchUserContent(Request $request)
    {
        $post = $request->all();
        $validator = Validator::make($post, [
            'username' => 'required',
        ]);

        if ($validator->fails()) {
            return ['code' => '201', 'msg' => '参数错误'];
        }


        $user = new User();
        $id = $user->select('id')->where('name', $post['username'])->first();
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
            $user_name = $request->user;
            $page = $request->curr ? $request->curr : 1;//当前页
            $num = $request->nums ? $request->nums : 10;//每页显示的数量


            $user = new  User();
            $userid = $user->select('id')->where('name', $user_name)->first();


//        $rev='每页显示的数量';
            $offset = ($page - 1) * $num;

            if ($user_name) {
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
                            ->where('jd_st','!=','1')
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    })
                    ->orWhere(function ($query) use ($userid) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where(['user_id' => $userid->id])
                            ->where('jd_st', '1')
                            ->where('tb_st','!=','1')
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
                $nums = DB::table('sms_contents')->where('status', '1')->wherebetween('updated_at', [Carbon::today(), Carbon::tomorrow()])
                    ->orWhere(function ($query) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where('tb_st', 1)
                            ->where('jd_st','!=',1)
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    })
                    ->orWhere(function ($query) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where('tb_st','!=',1)
                            ->where('jd_st', '=',1)
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    })
                    ->count();
                $datas = SmsContent::select('phone', 'province', 'content', 'sms_contents.updated_at')
                    ->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
                    ->where('sms_contents.status', '1')
                    ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()])
                    ->orWhere(function ($query) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where('tb_st', 1)
                            ->where('jd_st','!=',1)
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    })
                    ->orWhere(function ($query) {
                        $query->where(['sms_contents.status' => '0'])
                            ->where('tb_st','!=',1)
                            ->where('jd_st', '=',1)
                            ->whereBetween('sms_contents.updated_at', [Carbon::today(), Carbon::tomorrow()]);
                    })
                    ->orderby('sms_contents.updated_at', 'desc')
                    ->limit($num)
                    ->offset($offset)
//                    ->toSql();
//                dd ($datas);
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

            $contents = SmsContent::select(DB::raw('count(*) as total,province'))->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id');
            $countphones = PhoneNumber::select(DB::raw('count(*) as number,province'));
            if ($start) {
                $contents->where('sms_contents.updated_at', '>=', $start);
                $countphones->where('created_at', '>=', $start);

            }
            if ($end) {

                $contents->where('sms_contents.updated_at', '<=', $end);
                $countphones->where('created_at', '<=', $end);

            }
            if ($pro) {
                $contents->where('phone_numbers.province', '=', $pro);
                $countphones->where('province', '=', $pro);

            }
            $provinces = $contents->where('sms_contents.status', '1')
                ->orWhere(function ($query) {
                    $query->where('jd_st', '1')
                        ->where('tb_st','!=','1')
                        ->where('sms_contents.status', '0');
                })
                ->orWhere(function ($query) {
                    $query->where('tb_st', '1')
                        ->where('jd_st','!=','1')
                        ->where('sms_contents.status', '0');
                })
                ->groupby('province')->get()->toarray();
//               ->groupby('province')->toSql();;
            $phones = $countphones->where('status', '1')->groupby('province')->get()->toarray();

            $datas = $provinces;

            foreach ($provinces as $key => $province) {
                foreach ($phones as $phone) {
                    if ($province['province'] === $phone['province']) {
                        $datas[$key]['number'] = $phone['number'];

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
                'data' => $datas
            ]);

        } else {

            $provinces = SmsContent::select(DB::raw('count(*) as total,province'))
                ->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
                ->where('sms_contents.status', '1')
                ->orWhere(function ($query) {
                    $query->where('jd_st', '1')->where('tb_st','!=','1')
                        ->where('sms_contents.status', '0');
                })
                ->orWhere(function ($query) {
                    $query->where('tb_st', '1')->where('jd_st','!=','1')
                        ->where('sms_contents.status', '0');
                })
                ->groupby('province')
                ->get()
                ->toarray();
            return view('cfcc.index.month_detail', compact('provinces'));
        }
    }

    /**今日筛选数据统计
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function to_filter_detail()
    {
        $start = date('YmdHis', strtotime(Carbon::today()));
        $end = date('YmdHis', strtotime(Carbon::tomorrow()));
        $c= $this->filter_detail($start,$end);
        return view('cfcc.index.filter_detail', compact('c'));

    }

    /**昨日筛选数据统计
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ye_filter_detail()
    {
        $start = date('YmdHis', strtotime(Carbon::yesterday()));
        $end = date('YmdHis', strtotime(Carbon::today()));
        $c= $this->filter_detail($start,$end);
        return view('cfcc.index.filter_detail', compact('c'));
    }
    //所有的筛选数据统计
    public function all_filter_detail()
    {
        $c= $this->filter_detail();
        return view('cfcc.index.filter_detail', compact('c'));
    }
    /**数据统计
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filter_detail($start='',$end='')
    {
        if (!empty($start)){
            $all_phone = array_map('get_object_vars', DB::table('web_sms_prepare')
                ->select(DB::raw('count(*) as total,province'))->where('send', '5')
                ->whereBetween('addtime', [$start, $end])->orWhere('send', '0')->whereBetween('addtime', [$start, $end])->groupby('province')->get()->toarray());
            $send_phone = array_map('get_object_vars', DB::table('web_sms_prepare')->select(DB::raw('count(*) as nun,province'))->where('send', '5')->whereBetween('addtime', [$start, $end])->groupby('province')->get()->toarray());

        }else{
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
//            if(!$start){
//                $datas = $datas->where('created_at', '>=', Carbon::today());
//                $datas = $datas->where('created_at', '<=', Carbon::tomorrow());
//            }
            if ($start) {
                $datas = $datas->where('created_at', '>=', $start);
            }
            if ($end) {
                $datas = $datas->where('created_at', '<=', $end);
            }
            if (isset($type)) {
                $datas = $datas->where('type', '=', "$type");

            }
            $datas = $datas->orderby('created_at', 'desc');
            $nums = $datas->count();
            $success_rate = $datas->get()->toarray();
            $results = $datas->limit($num)->offset($offset)->get()->toarray();
//               $results=$datas ->limit($num)->offset($offset)->toSql();
//            dd($results);
//二维数组 京东 淘宝 成功失败
            $jd_success = $tb_success = $jd_fail = $tb_fail = 0;
            foreach ($success_rate as $result) {
                if ($result->type == '0') $result->result == '成功'||$result->result == '' ? $jd_success += 1 : $jd_fail += 1;
                if ($result->type == '1') $result->result == '成功'||$result->result == '' ? $tb_success += 1 : $tb_fail += 1;

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
