<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    protected array $dontReference = [
        ModelNotFoundException::class,
        HttpException::class,
        AuthenticationException::class,
        NotFoundHttpException::class,
        ThrottleRequestsException::class,
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
            && (!in_array(get_class($exception), $this->dontReference) || $exception->getCode() === 500)
        ) {
            $name = get_class($exception);
            //ToDo: $exception = new Referencable();
            Log::error(sprintf('Reference for above exception of type %s: %s', $name, 'nonexistent-reference'));
        }

        $response = parent::render($request, $exception);

        if ($response instanceof JsonResponse && !config('app.debug') && $exception instanceof Referencable) {
            $response->setData($response->getData(true) + ['reference' => $exception->reference]);
        }

        return $response;
    }

    /**
     * Builds the exception's context array.
     *
     * @return array
     */
    protected function context(): array {
        try {
            return array_merge(parent::context(), [
                'url' => request()?->fullUrl(),
            ]);
        } catch (Throwable) {
            // If request() is not available (e.g. in Artisan commands), return the default context.
            return parent::context();
        }
    }
}
