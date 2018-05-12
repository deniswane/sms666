<?php

namespace App\Exceptions;

use Mail;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Mail\ExceptionOccured;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            $this->sendEmail($exception);
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof   \Illuminate\Auth\Access\AuthorizationException) {

            return response()->view('errors.403', [], 403);
        }
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->view('errors.403', [], 403);
        }

        if ($exception instanceof   \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }
        if ($exception instanceof   \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }
        if ($exception instanceof  \Symfony\Component\HttpKernel\Exception\HttpException) {
            return response()->json(['code'=>103,'msg'=>'The frequency is too fast']);
        }

        return parent::render($request, $exception);
    }

    /**
    +     * Sends an email to the developer about the exception.
    +     *
    +     * @param  \Exception  $exception
    +     * @return void
    +     */
    public function sendEmail(Exception $exception)
     {
        try {
            $e = FlattenException::create($exception);
            $handler = new SymfonyExceptionHandler();

            $html = $handler->getHtml($e);

            Mail::to('641268939@qq.com')->send(new ExceptionOccured($html));
            Mail::to('947848875@qq.com')->send(new ExceptionOccured($html));
        } catch (Exception $ex) {
//                dd($ex);
           }
    }
}
