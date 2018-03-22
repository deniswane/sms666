<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $table = 'users';

    /**
     * boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
     */
    /*public static function boot(){
        parent::boot();
        static ::creating(function ($user){
            $user->activation_token = str_random(30);
        });
    }*/
}
