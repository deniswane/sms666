<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
//use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Jrean\UserVerification\Traits\UserVerification;
class Admin extends Authenticatable
{
    use Notifiable;
    use UserVerification;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'admins'; //表名
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];
}
