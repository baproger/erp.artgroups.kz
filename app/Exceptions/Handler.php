<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Одна красивая страница на все HTTP-ошибки (403, 404, 419, 429, 503 …)
        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                return null; // для API/AJAX оставляем JSON-ответ
            }

            return response()->view('errors.403', [
                'code'      => $e->getStatusCode(),
                'exception' => $e,
            ], $e->getStatusCode(), $e->getHeaders());
        });
    }
}
