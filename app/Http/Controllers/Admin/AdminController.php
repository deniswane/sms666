<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Library\Y;

class AdminController extends Controller
{
    //修改个人信息
    public function me(Request $request)
    {
        if ($request->isMethod('post')) {
            $post = $request->post();
            $validator = Validator::make($post, [
                'nickname' => 'max:64',
            ]);

            if ($validator->fails()) {
                return Y::error($validator->errors());
            }

            $data = Admin::find($post['user_id'])->toArray();


            if (isset($post['password'])) {
                if ($post['password'] !== $post['password_confirmation']) {
                    return Y::error('两次输入的密码不一致！');
                }
            }

            $data['name'] = $post['nickname'];
            $data['password'] = Hash::make($post['password']);
            $data['updated_at'] = date('Y:m:d H:i:s', time());

            if (Admin::where('id', $post['user_id'])->update($data) > 0) {
                Auth::guard('admin')->logout();
                return Y::success('修改成功', [], route('cfcc.login'));
            }
            return Y::error('修改失败');
        } else {
            return view('cfcc.admin.edit', [
                'user' => Auth::guard('admin')->user()
            ]);
        }
    }
}
