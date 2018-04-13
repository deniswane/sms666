<?php
namespace App\Observers;
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/13
 * Time: 11:12
 */

class SmsContentObserver {
    public function created(\App\Models\SmsContent $content){
        $content->is_changed = true;
    }
}