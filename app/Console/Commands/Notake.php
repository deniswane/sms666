<?php

namespace App\Console\Commands;

use App\Models\SmsContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Notake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
       $phones = SmsContent::select('phone')->leftjoin('phone_numbers','phone_numbers.id','=','sms_contents.phone_number_id')
            ->where('sms_contetns.status','0')->get()->toarray();
       foreach ($phones as  $phone){
           DB::table('web_prepare')->where('phone',$phone->phone)->update(['send'=>'0']);
       }
    }
}
