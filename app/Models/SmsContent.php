<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PhoneNumber;

/**
 * Class SmsContent
 * @package App\Models
 * 如果没有一对多的关系，我们需要这样来创建一条微博。
    App\Models\Status::create()
    当我们将用户模型与微博模型进行一对多关联之后，我们得到了以下方法。
    $user->statuses()->create()
    这样在微博进行创建时便会自动关联与微博用户之间的关系，非常方便。
 */
class SmsContent extends Model
{
    public function phone() {
        // 属于xx  1对多 括号里是1
        return $this->belongsTo(PhoneNumber::class,'phone_number_id');
    }
}
