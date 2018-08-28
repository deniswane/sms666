<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(Request $request ,User $user)
    {
        $page = $request->curr ? $request->curr : 1;//当前页
        $num = $request->nums ? $request->nums : 10;//每页显示的数量
        //        $rev='每页显示的数量';
        $offset = ($page - 1) * $num;
        $nums = User::count();
        $data =  $user ->limit($num)->offset($offset)->get();

        if ($request->isMethod('post')){
            $nums =$user->count();
            return response()->json([
                'code' => '',
                'msg' => '',
                'count' => $nums,
                'data' => $data,
            ]);
        }
        return view('cfcc.index.user_manager');
    }

    public function curd(Request $request,User $user)
    {
        $name =$request->name;
        $email =$request->email;
        $password =$request->password;
        $re_password =$request->re_password;
        $type=$request->type;
        $price=$request->price;
        $token=md5(uniqid() . $email);

        if($request->isMethod('post')){
            if ($password !==$re_password){
                return ['code'=>403,'msg'=>'两次输入的密码不一致！'];
            }
//           try{
              $password= Hash::make($password);
            $res= $user ->insertGetId(['name'=>$name,'email'=>$email,'password'=>$password,'token'=>$token,'verified'=>'1','created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
                ]);

            $type_name =DB::table('type_config')->where('id',$type)->value('type_name');
            DB::table('configs')->insert(['type_name'=>$type_name,'type_id'=>$type,'price'=>$price,'user_id'=>$res]);

            if ($res) return ['code'=>200,'msg'=>'添加成功！'];
//          }catch (\Exception $e){
//               return ['code'=>202,'msg'=>'添加失败，请确认邮箱是否已经存在！'];
//           }
        }elseif ($request->isMethod('put')){

            if ($password !==$re_password){
                return ['code'=>403,'msg'=>'两次输入的密码不一致！'];
            }
            $password= Hash::make($password);

            $res = $user ->where('email',$email)->update(['name'=>$name,'password'=>$password,'updated_at'=>Carbon::now()]);

                if ($res) {
                    return ['code'=>200,'msg'=>'更新成功！'];
                } else{
                    return ['code'=>202,'msg'=>'更新失败！'];
                }
        }
        $types=DB::table('type_config')->select('id','type_name')->get();
        return view('cfcc.index.user_manager_list',compact('types'));
    }

    public function delete(Request $request,User $user)
    {
        $id =$request->id;
        if ($id){
            $res=$user->where('id',$id)->delete();
            //删除用户后删除configs表对应的单价配置
            $type_config=DB::table('configs')->select('id')->where('user_id',$id)->get();
            if ($type_config){
                DB::table ('configs')->where('user_id',$id)->delete();
            }
            if ($res){
                return ['code' => 200, 'msg' => '删除成功！'];
            }else{
                return ['code' => 202, 'msg' => '删除失败！'];
            }
        }
    }

    public function reset(Request $request,User $user)
    {
        $id =$request->id;
        $token=md5(uniqid() . $user->email);

        if ($id){
            $res=$user->where('id',$id)->update(['token'=>$token]);
            if ($res){
                return ['code' => 200, 'msg' => '重置成功！'];
            }else{
                return ['code' => 202, 'msg' => '重置失败！'];
            }
        }
    }
}
