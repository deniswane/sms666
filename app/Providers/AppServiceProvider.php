<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\SmsContentObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        DB::listen(function($query) {
//            $tmp = str_replace('?', '"'.'%s'.'"', $query->sql);
//            $tmp = vsprintf($tmp, $query->bindings);
//            $tmp = str_replace("\\","",$tmp);
//            Log::info($tmp."\n\n\t");
//        });
        User::observe(UserObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
