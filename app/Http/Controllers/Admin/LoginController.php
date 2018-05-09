<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Library\Y;
class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {    config('locale','zh-CN');
        $this->middleware('guest:admin', ['except' => 'logout']);
    }

    //登陆
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $post      = $request->only(['name', 'password']);
            $validator = Validator::make($post, [
                'name' => 'required',
                'password' => 'required'

            ]);
            if ($validator->fails()) {
                return Y::error($validator->errors());
            }
            if (Auth::guard('admin')->attempt($post, boolval($request->post('remember', '')))) {
                return Y::success('登录成功', [], route('admin.index'));
            }
            return Y::error('用户验证失败');
        } else {
            return view('admin.login.index');
        }
    }
    //退出
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('admin/login');

    }
    //自定义认证驱动
    protected function guard()
    {
        return auth()->guard('admin');
    }


}
