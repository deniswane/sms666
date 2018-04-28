<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/
//require '../vendor/autoload.php';
//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;
//use Monolog\Handler\SwiftMailerHandler;

require __DIR__.'/helpers.php';

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

//log

$app->configureMonologUsing(function($monolog) {

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_debug.log'), Monolog\Logger::DEBUG, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));
    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_info.log') , Monolog\Logger::INFO,false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, false, true)));

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_notice.log') , Monolog\Logger::NOTICE, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_warning.log') , Monolog\Logger::WARNING, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_error.log') , Monolog\Logger::ERROR, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_critical.log') , Monolog\Logger::CRITICAL, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_alert.log') , Monolog\Logger::ALERT, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));

    $monolog->pushHandler((new Monolog\Handler\StreamHandler(storage_path('logs/laravel_emergency.log') , Monolog\Logger::EMERGENCY, false))
        ->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true)));

});
/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
