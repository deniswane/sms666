<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SmsContent;

class PhoneNumber extends Model
{
    // 短信内容
    public function smsContents() {
        return $this->hasMany(SmsContent::class,'phone_number_id','id');
    }

}
