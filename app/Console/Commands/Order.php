<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
class Order extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update 12:00';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $yes = date('Ymd')-1;
        $tablename = "SMS" .$yes . '6666';

        $order_res = DB::connection('ourcms')->table('cms_order')
            ->select('id')
            ->where('order_name', '=', $tablename)
            ->where('state', '!=', '-1')
            ->where('state', '!=', '-2')
            ->orderby('addtime','desc')
            ->first();
        if ($order_res) {
            # 订单总表里的id 对应 外边订单详细表的表名
            $ordtb = "cms_orddata_" . $order_res->id;
            $order_table = DB::connection('ourcms')->table($ordtb);
            $overdue = date("Y-m-d H:i:s",time()-180);//三分钟
                $opens = $order_table->select('phone','id')
                    ->where('return_times','0')
                    ->where('nowtime','<=',$overdue)
                    ->where('smstext','=','xuxxq61!v7q6amieklnmmkgi')
                    ->orWhere(function ($query)use($overdue) {
                        $query ->where('smstext','=','xuxxq61!v7q6amknhmmeimhl')
                            ->where('return_times','0')
                            ->where('nowtime','<=',$overdue);
                    })
                    ->orWhere(function ($query)use($overdue) {
                        $query ->where('smstext','=','xuxxq61!v7q6amklkikhjlln')
                            ->where('return_times','0')
                            ->where('nowtime','<=',$overdue);
                    })
                    ->orWhere(function ($query)use($overdue) {
                        $query ->where('smstext','=','xuxxq61!v7q6amihinnejmni')
                            ->where('return_times','0')
                            ->where('nowtime','<=',$overdue);
                    })
                    ->get()->toarray();

            //批量更新
//            DB::update(DB::raw("UPDATE sms_contents SET tb_st = 1,jd_st=1 WHERE jd_st = 0 "));
            $n=$m=0;

            if ($opens){

                foreach ($opens as $key =>$open){
                    $new_data = [
                        'phone' =>$open->phone ,
                        'smstext' => 'xuxxq61!p5vxq',
                        'nowtime' => date("Y-m-d H:i:s"),
                        'software' => '',
                    ];
                    $update= DB::connection('ourcms')->table($ordtb)->where('id', $open->id)->update(['return_times'=>'1']);
                    if($update) ++$n;
                    $insert=$order_table->insert($new_data);
                    if ($insert) ++$m;
                }
            }

            $dt= Carbon::now().'凌晨十二点调用成功完成,数据表'.$ordtb.'更新'.$n.'条，插入'.$m.'条；';
            Storage::disk('local')->append('cron.txt',$dt);
        }

    }
}
