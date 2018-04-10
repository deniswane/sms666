<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    //用户授权
    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }
    //后台访问
//    public function access(Admin $currentUser, Admin $user)
//    {
//        return $currentUser->is_admin;
//    }

}
