<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ErrorMailer;
use Telegram\Bot\Laravel\Facades\Telegram;

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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // $this->reportable(function (\Exception $e) {
        //     Mail::to(config('initial-values.error_mailer_receiver'))
        //         ->queue(new ErrorMailer($e->getMessage()));
        // });
        $this->reportable(function (\Exception $e) {
            if (config('initial-values.deliver_errors_via_telegram')) {
                Telegram::sendMessage([
                    'chat_id' => config('initial-values.projects_error_receiver_chat_id'),
                    'parse_mode' => 'HTML',
                    'text' => 'Project: ' . config('app.name') . ': ' . substr($e->getMessage(), 0, 255)
                ]);
            }
        });
        if ($this->isApiCall()) {
            $this->renderable(function (NotFoundHttpException $e) {
                return response()->json(['code' => 404, 'data' => 'Not Found']);
            });
            $this->renderable(function (AuthenticationException $e) {
                return response()->json(['code' => 401, 'data' => 'Unauthenticated']);
            });
            $this->renderable(function (\Exception $e) {
                return response()->json(['data' => 'Exception']);
            });
        }
    }
    
    protected function isApiCall()
    {
        return request()->segment(1) == 'api' ?? false;
    }
}
