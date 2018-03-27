<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhoneNumber;

class PhonecController extends Controller
{
    // 短信内容列表
    public function detailSms(PhoneNumber $number){
        $contents = $number->smsContents()
                           ->orderby('created_at','desc')
                           ->paginate(30);
        return view('layouts.detail_sms_content_',compact('number','contents'));
    }

}
