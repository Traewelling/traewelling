<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        OAuthServerException::class
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
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Throwable $exception
     *
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception) {
        // create referencable exception, if running in production, not already referencable and not maintenance mode
        if (
            !config('app.debug')
            && !$exception instanceof Referencable
            && !($exception instanceof HttpException && $exception->getStatusCode() === 503)
        ) {
            $exception = new Referencable();
            Log::error('Reference for above exception: '.$exception->reference);
        }

        $response = parent::render($request, $exception);

        if ($response instanceof JsonResponse && !config('app.debug') && $exception instanceof Referencable) {
            $response->setData($response->getData(true) + ['reference' => $exception->reference]);
        }

        return $response;
    }
}
