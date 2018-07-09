<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Jrean\UserVerification\Traits\UserVerification;
use Carbon\Carbon;

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
        'password', 'remember_token'
    ];

    protected $table = 'users';

    /**
     * boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->token = md5(uniqid() . $user->email);
        });
        static::created(function ($user) {
            $dayEnd = date('Y:m:d H-i-s', strtotime(date('Y-m-d', time())) + 86400);

            DB::table('page_views')->insert(['user_id' => $user->id, 'daliy_amount' => 0, 'amounts' => 0, 'expiration_time' => $dayEnd]);
        });
    }

    //关联手机号数据表
    public function phone()
    {
        return $this->hasMany(PhoneNumber::class, 'user_id');
    }

    /**获取取走短信内容成功的数量
     * @param $userid
     * @return array
     */
    public function getSmsNumber($userid = '')
    {
        //昨天的手机号id按省份分类
        $extend = 1440 - date('H', time()) * 60 + date('i', time());

        if (empty($userid)) {
            if (!Cache::has('yes_phone_count')) {
                $yes_sms_count = $this->getContentsCount([Carbon::yesterday(), Carbon::today()]);
                Cache::put('yes_phone_count', $yes_sms_count, $extend);
            } else {
                $yes_sms_count = Cache::get('yes_phone_count');
            }

            $to_sms_count = $this->getContentsCount([Carbon::today(), Carbon::tomorrow()]);
        } else {
            if (!Cache::has('yes_sms_count' . $userid)) {
                $yes_sms_count = $this->getContentsCount([Carbon::yesterday(), Carbon::today()], $userid);
                Cache::put('yes_sms_count' . $userid, $yes_sms_count, $extend);
            } else {
                $yes_sms_count = Cache::get('yes_sms_count' . $userid);
            }

            $to_sms_count = $this->getContentsCount([Carbon::today(), Carbon::tomorrow()], $userid);

        }
        return ['yes_phone' => $yes_sms_count, 'to_phone' => $to_sms_count];
    }

    /**获取取走短信手机号
     * @param $userid
     * @return array
     */
    public function getNumber($userid = '')
    {
        $extend = 1440 - date('H', time()) * 60 + date('i', time());

        if (!empty($userid)) {
            if (!Cache::has('yes_taked_' . $userid)) {
                $yes_taked = $this->countPhone([Carbon::yesterday(), Carbon::today()], $userid);
                Cache::put('yes_taked_' . $userid, $yes_taked, $extend);
            } else {
                $yes_taked = Cache::get('yes_taked_' . $userid);
            }

            $to_taked = $this->countPhone([Carbon::today(), Carbon::tomorrow()], $userid);

        } else {
            if (!Cache::has('yes_taked_count')) {
                $yes_taked = $this->countPhone([Carbon::yesterday(), Carbon::today()]);

                Cache::put('yes_taked_count', $yes_taked, $extend);
            } else {
                $yes_taked = Cache::get('yes_taked_count');
            }

            $to_taked = $this->countPhone([Carbon::today(), Carbon::tomorrow()]);
        }
        return ['yes_phone' => $yes_taked, 'to_phone' => $to_taked];
    }

    /**昨天、今天被取走的手机号id
     * @param $id
     * @param $arr
     * @param $select
     * @return mixed
     */
    public function countPhone($arr, $id = '')
    {
        if (!empty($id)) {
            $userPhone = $this->find($id)->phone()
                ->select('id', 'province');
        } else {
            $userPhone = PhoneNumber::select('id', 'province');
        }
        $phones = $userPhone->whereBetween('created_at', $arr)
            ->where(['status' => '1'])
//            ->orderby(length())
            ->get()
            ->toArray();
        $province = [];
        foreach ($phones as $phone) {
            $province[$phone['province']][] = $phone['id'];
        }
        return $province;
    }

    /**取走短信的数量
     * @param $data
     * @param $start
     * @param $end
     * @return array
     */
    private function getContentsCount($arr, $userid = '')
    {
        $num = $bs = [];

        if (!empty($userid)) {
            $datas = SmsContent::select('sms_contents.id', 'province')
                ->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
				->where('sms_contents.status','=','1')
                ->where('phone_numbers.user_id','=',$userid )
                ->wherebetween('sms_contents.updated_at', $arr)
           ->orWhere(function($query) use($arr)
           {
             $query->where('tb_st','1')->where('jd_st','0')
					->wherebetween('sms_contents.updated_at', $arr);

        })
            ->orWhere(function($query) use($arr)
            {
                $query  ->where('tb_st','0')->where('jd_st','1')
					->wherebetween('sms_contents.updated_at', $arr)

                ;
            })
                ->orderby('sms_contents.updated_at', 'desc')
                ->get()
                ->toArray();
//
        }else{
            $datas = SmsContent::select('sms_contents.id', 'province')
                ->leftjoin('phone_numbers', 'phone_number_id', '=', 'phone_numbers.id')
                ->where('sms_contents.status','1')
				->wherebetween('sms_contents.updated_at', $arr)
               ->orWhere(function($query) use($arr)
           {
                $query->where(['sms_contents.status'=>'0' ])
                    ->where('tb_st','1')->where('jd_st','0')
					->wherebetween('sms_contents.updated_at', $arr)

                ;
            })
            ->orWhere(function($query) use($arr)
            {
                $query->where(['sms_contents.status'=>'0'])
                    ->where('jd_st','1')->where('tb_st','0')
					->wherebetween('sms_contents.updated_at', $arr)

                ;
           })

                ->orderby('sms_contents.updated_at', 'desc')
                ->get()
                ->toArray();
        }

        foreach ($datas as $key => $data) {
            $bs[$data['province']][] = $data['id'];
        }
        foreach ($bs as $key => $b) {
            $num[$key] = count($b);
        }
        return $num;

    }
}
