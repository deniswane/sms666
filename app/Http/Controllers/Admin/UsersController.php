<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Carbon\Carbon;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    public function index(Request $request, User $user)
    {
        $page = $request->curr ? $request->curr : 1;//当前页
        $num = $request->nums ? $request->nums : 10;//每页显示的数量
        //        $rev='每页显示的数量';
        $offset = ($page - 1) * $num;
        $nums = User::count();
        $data = $user->limit($num)->offset($offset)->get();

        if ($request->isMethod('post')) {
            $nums = $user->count();
            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $data,
            ]);
        }
        return view('cfcc.index.user_manager');
    }

    public function curd(Request $request, User $user)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $re_password = $request->re_password;
        $type = $request->type;
        $price = $request->price;
        $token = md5(uniqid() . $email);

        if ($request->isMethod('post')) {
            if ($password !== $re_password) {
                return ['code' => 403, 'msg' => '两次输入的密码不一致！'];
            }
//           try{
            $password = Hash::make($password);
            $res = $user->insertGetId(['name' => $name, 'email' => $email, 'password' => $password, 'token' => $token, 'verified' => '1', 'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $type_name = DB::table('type_config')->where('id', $type)->value('type_name');
            DB::table('configs')->insert(['type_name' => $type_name, 'type_id' => $type, 'price' => $price, 'user_id' => $res]);

            if ($res) return ['code' => 200, 'msg' => '添加成功！'];
//          }catch (\Exception $e){
//               return ['code'=>202,'msg'=>'添加失败，请确认邮箱是否已经存在！'];
//           }
        } elseif ($request->isMethod('put')) {

            if ($password !== $re_password) {
                return ['code' => 403, 'msg' => '两次输入的密码不一致！'];
            }
            $password = Hash::make($password);

            $res = $user->where('email', $email)->update(['name' => $name, 'password' => $password, 'updated_at' => Carbon::now()]);

            if ($res) {
                return ['code' => 200, 'msg' => '更新成功！'];
            } else {
                return ['code' => 202, 'msg' => '更新失败！'];
            }
        }
        $types = DB::table('type_config')->select('id', 'type_name')->get();
        return view('cfcc.index.user_manager_list', compact('types'));
    }

    public function delete(Request $request, User $user)
    {
        $id = $request->id;
        if ($id) {
            $res = $user->where('id', $id)->delete();
            //删除用户后删除configs表对应的单价配置
            $type_config = DB::table('configs')->select('id')->where('user_id', $id)->get();
            if ($type_config) {
                DB::table('configs')->where('user_id', $id)->delete();
            }
            if ($res) {
                return ['code' => 200, 'msg' => '删除成功！'];
            } else {
                return ['code' => 202, 'msg' => '删除失败！'];
            }
        }
    }

    public function reset(Request $request, User $user)
    {
        $id = $request->id;
        $token = md5(uniqid() . $user->email);

        if ($id) {
            $res = $user->where('id', $id)->update(['token' => $token]);
            if ($res) {
                return ['code' => 200, 'msg' => '重置成功！'];
            } else {
                return ['code' => 202, 'msg' => '重置失败！'];
            }
        }
    }

    public function allot_phones(Request $request)
    {

        //取三天前
        $end = date('Ymd', strtotime("-3 day")) . '235959';
        $ids = DB::table('type_config')->select('id', 'type_name')->get()->toarray();

        $data = [];
        //未分配的
        foreach ($ids as $id) {
            $sql = DB::table('web_sms_prepare');
            if ($id->type_name == '京东') {
                $sql = $sql->where('send', 0)
                    ->whereRaw("locate(0,user_id)>0");

                $a = $sql->whereRaw("locate($id->id,type_id)>0")->count();

            } else {

                //其它类别三天前的
                $a = $sql->where('send', 5)->where('addtime', '<', $end)->whereRaw("locate(-($id->id),type_id)=0")->whereRaw("locate($id->id,type_id)=0")->count();
            }
            $data[$id->type_name]['un_allot'] = $a;
        };

        //表格数据
        if ($request->isMethod('post')) {

            $page = $request->curr ? $request->curr : 1;//当前页
            $nu = $request->nums ? $request->nums : 10;//每页显示的数量

//        $rev='每页显示的数量';
            $offset = ($page - 1) * $nu;

            $sq = DB::table('configs')->select('configs.id', 'name', 'type_name', 'type_id', 'user_id')
                ->leftjoin('users', 'user_id', 'users.id')
                ->orderby('type_id');
            $nums = $sq->count();

            $users = $sq->limit($nu)->offset($offset)
                ->get()->toarray();


            foreach ($users as $user) {
                if ($user->type_name == '截取') {
                    $user->type_name = '京东';
                    $user->type_id = DB::table('type_config')->where('type_name', '京东')->value('id');
                }

                $sql = DB::table('web_sms_prepare');
                if (!$user->type_name == '京东') {
                    $sql = $sql->where('addtime', '<', $end);
                }
                $c = $sql->whereRaw("locate($user->user_id,user_id)>0")->whereRaw("locate(-($user->type_id),type_id)>0")->count();

                if (isset($data[$user->type_name])) {

                    $user->num = $data[$user->type_name]['un_allot'];
                    $user->alloat = $c;
                }

            }

            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $users,
            ]);
        }
        //单个修改
        if ($request->isMethod('put')) {
           $met= $request->_met;
           if ($met == 'edit'){
               $num = $request->num;
               $max = $request->max;
               $id = $request->id;
               if (empty($num)) {
                   return ['code' => 403, 'msg' => '不能为空'];
               }
               if (!is_int($num * 1)) {
                   return ['code' => 201, 'msg' => '请输入整数！'];
               }
               if ($num > $max) {
                   return ['code' => 202, 'msg' => '手机号没那么多！'];
               }
               $user = DB::table('configs')->select('type_id', 'type_name', 'user_id')->where('id', $id)->first();

               //京东 与其他的区别  send=0;
               if ($user->type_name == '京东' || $user->type_name == '截取') {

                   //更新user_id 更新 替换 type_id -1

                   $res = DB::table('web_sms_prepare')->where('send', 0)->where('type_id', '1')->orderby('addtime', 'desc')->limit($num)->update(['type_id' => '-1', 'user_id' => $user->user_id]);


               } else {
                   $res = DB::table('web_sms_prepare')->select('id', 'user_id', 'type_id')->where('send', 5)
                       ->where('addtime', '<', $end)
                       ->whereRaw("locate($user->type_id,type_id)=0")
                       ->orderby('addtime', 'desc')
                       ->limit($num)->get();
                   foreach ($res as $re) {
                       //user_id默认0
                       if ($re->user_id == '0') {
                           $dat['user_id'] = $user->user_id;
                       } else {
                           $users_id = explode(',', $re->user_id);
                           if (in_array($user->user_id, $users_id)) {
                               $dat['user_id'] = $re->user_id;
                           } else {
                               $user_id = $re->user_id . ',' . $user->user_id;
                               $dat['user_id'] = $user_id;
                           }
                       }
                       $dat['type_id'] = $re->type_id . ',-' . $user->type_id;
                       DB::table('web_sms_prepare')->where('id', $re->id)->update($dat);

                   }
               }
               if ($res) {
                   return ['code' => 200, 'msg' => '成功'];
               } else {
                   return ['code' => 400, 'msg' => '失败'];
               }
           }
           elseif ($met =='del'){


               $type_id=$request->type_id;
               $user_id=$request->user_id;
               //如果是京东 直接换
               if($type_id=='1'){
                   $res= DB::table('web_sms_prepare')
                       ->where('user_id',$user_id)
                       ->where('type_id',"-1")
                       ->update(['user_id'=>'0','type_id'=>'1']);

               }else{
                   //其它品种 查找替换
                   $res=  DB::table('web_sms_prepare')->select('id','type_id','user_id')
                       ->orderby('addtime') ->whereRaw("locate(-$type_id,type_id)>0")->whereRaw("locate($user_id,user_id)>0")
                        ->chunk(100, function ($users) use ($type_id) {
                           foreach ($users as $user) {
                               $type_ids=explode(',',$user->type_id);
                              foreach ($type_ids as $k => &$ty_id){
                                  if($ty_id == "-$type_id"){
                                      unset($type_ids[$k]);
                                  }
                              }
                              $tyids=implode(',',$type_ids);
                              DB::table('web_sms_prepare')->where('id',$user->id)->update(['type_id'=>$tyids]);
                           }
                   });
               }
               if ($res)
               {
                   return ['code'=>200,'msg'=>'清零成功'];
               }

           }

        }


        return view('cfcc.index.allot_phones', compact('data'));

    }

    public function auto_allot_phones(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = json_decode($request->dat, true);

            foreach ($data as $b) {
                if ($b > 100) {
                    return ['msg' => '数字太大了'];
                }
            }

            foreach ($data as $key => $dat) {
                DB::table('configs')->where('id', $key)->update(['percent' => $dat]);
            }
            return ['code' => 200, 'msg' => '更新成功'];
        }
        if ($request->isMethod('put')) {
            $open = $request->open;
            $res = DB::table('all_configs')->update(['switch' => $open]);
            if ($res){
                return ['msg'=>'成功'];
            }
        }

        $type_id = DB::table('type_config')->whereIn('type_name', ['京东', '截取'])->pluck('id')->toarray();

        $configs = DB::table('configs')->select('configs.id', 'name', 'percent')->leftjoin('users', 'user_id', '=', 'users.id')->whereIn('type_id', $type_id)->get();
        return view('cfcc.index.auto_allot_phones', compact('configs'));
    }
}
