<?php
namespace App\Observers;
use App\Models\User;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/12
 * Time: 9:59
 */

class UserObserver {

    public function saved(User $user){
        $user->token = md5($user->email.random_int(1,999));
    }
}