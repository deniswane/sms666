<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //paypal/notify
        'alinotify*',
        'paypal/notify',
        'manager/api/sendmsg',
        'manager/api/inside/content',
        'manager/api/remote_close'
    ];
}
