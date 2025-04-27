<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        
        // Custom handling for API responses
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => $e->errors(),
                    ], 422);
                }
                
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                    ], 401);
                }
                
                if ($e instanceof AccessDeniedHttpException) {
                    return response()->json([
                        'message' => 'This action is unauthorized.',
                    ], 403);
                }
                
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'message' => 'Resource not found.',
                    ], 404);
                }
                
                // Handle all other exceptions for API
                if (!config('app.debug')) {
                    return response()->json([
                        'message' => 'Server Error.',
                    ], 500);
                }
            }
        });
    }
}
