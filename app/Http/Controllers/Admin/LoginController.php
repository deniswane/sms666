<?php

namespace App\Http\Controllers\Admin;


    use App\Http\Controllers\Controller;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;
    use Illuminate\Support\Facades\Auth;
    use App\Library\Y;
    class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin/index';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => 'logout']);
    }

    /**
     * 重写登录视图页面
     */
    public function showLogin()
    {
        return view('admin.login.index');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('admin/login');

    }
    /**
     * 自定义认证驱动
     * @return mixed
     */
    protected function guard()
    {
        return auth()->guard('admin');
    }


}
