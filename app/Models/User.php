<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Jrean\UserVerification\Traits\UserVerification;

class User extends Authenticatable
{
    use Notifiable;
    use UserVerification;


    /**
     * The attributes that are mass assignable.
     *
     * @var arrayaaaaaaaaaaaaaaaaaaaaaa
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','token'
    ];

    protected $table = 'users';

    /**
     * boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
     */
    public static function boot(){
        parent::boot();
        static ::creating(function ($user){
            $user->token =md5(uniqid().$user->email) ;
        });
        static ::created(function ($user){
            $dayEnd = date('Y:m:d H-i-s',strtotime(date('Y-m-d', time()))+86400);

            DB::table('page_views')->insert(['user_id'=>$user->id,'daliy_amount'=>0,'amounts'=>0,'expiration_time'=>$dayEnd]);
        });
    }


}
