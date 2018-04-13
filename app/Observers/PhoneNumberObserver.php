<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/13
 * Time: 11:44
 */

namespace App\Observers;


class PhoneNumberObserver {
    // 有新号码时 放入 号码状态表
    public function created(\App\Models\PhoneNumber $number){
        DB::insert('insert into newest_sms_content (phone,is_changed) values (?, ?)', [$number->phone, true]);
    }
}