<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Modules\UserMangementModule\routes\api;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')->prefix('api')->group(
                base_path('Modules/UserMangementModule/routes/api.php')
            );
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function( Throwable $e, Request $request){
            if($request->expectsJson() && !$request->is('api/*')){
                return null;
            }

            if($e instanceof HttpResponseException){
                return $e->getResponse();
            }
            Log::error(
                "API Exception: ". get_class($e)." - Message: ".$e->getMessage()." - File: ".$e->getFile()." - line: ".$e->getLine()
            );

            $responseDetails = match(get_class($e)){
                ValidationException::class => [
                    'statusCode' => 422,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ],
                AuthenticationException::class => [
                    'statusCode' => 401,
                    'message' => 'Unauthenticated',
                ],
                AuthorizationException::class => [
                    'statusCode' => 403,
                    'message' => 'This action is unauthorized',
                ],
                NotFoundResourceException::class => [
                    'statusCode' => 404,
                    'message' => 'This requested resource was not found',
                ],
                default => [
                    'statusCode' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                    'message' => app()->isProduction() ? 'An unexpected server error occured' : $e->getMessage()

                ]

            };

            $payload = [
                'status' => 'error',
                'message' => $responseDetails ['message']
            ];

            if(!empty($responseDetails['errors'])){
                $payload['errors'] = $responseDetails['errors'];
            }

            $statusCode =( $responseDetails ['statusCode'] >= 400 && $responseDetails['statusCode']< 600)
                            ? $responseDetails['statusCode'] : 500;

            return response()->json($payload, $statusCode);
        });


    })->create();
