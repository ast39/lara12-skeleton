<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Переопределяем дефолтный redirect на login (чтобы не ломалось если где-то всё-таки вызовется)
        $middleware->redirectGuestsTo(fn () => '/login');

        $middleware->web(append: [
            //
        ]);

        $middleware->api(append: [
            //
        ]);

        $middleware->alias([
            //
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Для API всегда возвращать JSON, никаких редиректов
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Для всех API роутов возвращаем JSON вместо HTML
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // API: не редиректим, только 401 JSON
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $message = $e->getMessage() ?: 'Ошибка аутентификации';
                    $errorDto = new \App\Dto\ServerErrorDto($message, 401);

                    return \App\Http\Resources\Api\ErrorResource::make($errorDto)
                        ->response()
                        ->setStatusCode(401);
                }

                // Обработка ошибок валидации
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;

                    // Получаем все ошибки валидации
                    $errors = $e->errors();

                    // Формируем детальное сообщение из всех ошибок
                    $messages = [];
                    foreach ($errors as $field => $fieldErrors) {
                        foreach ($fieldErrors as $error) {
                            $messages[] = $error;
                        }
                    }

                    // Объединяем все сообщения в одну строку
                    $errorMessage = ! empty($messages)
                        ? implode(' ', $messages)
                        : 'Ошибка валидации данных';

                    $errorDto = new \App\Dto\ServerErrorDto(
                        $errorMessage,
                        $statusCode
                    );

                    return \App\Http\Resources\Api\ErrorResource::make($errorDto)
                        ->response()
                        ->setStatusCode($statusCode);
                }

                // Обработка 404 ошибок
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $statusCode = 404;
                    $errorDto = new \App\Dto\ServerErrorDto(
                        'Ресурс не найден',
                        $statusCode
                    );

                    return \App\Http\Resources\Api\ErrorResource::make($errorDto)
                        ->response()
                        ->setStatusCode($statusCode);
                }

                // JWT (php-open-source-saver/jwt-auth): 401 в JSON
                if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException) {
                    $message = $e->getMessage() ?: 'Ошибка аутентификации';
                    $errorDto = new \App\Dto\ServerErrorDto($message, 401);

                    return \App\Http\Resources\Api\ErrorResource::make($errorDto)
                        ->response()
                        ->setStatusCode(401);
                }

                // Обработка остальных ошибок
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                // Если код не валидный HTTP статус, ставим 500
                if (! is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
                    $statusCode = 500;
                }

                // Используем ErrorResource для консистентности
                $errorDto = new \App\Dto\ServerErrorDto(
                    $e->getMessage() ?: 'Внутренняя ошибка сервера',
                    $statusCode
                );

                return \App\Http\Resources\Api\ErrorResource::make($errorDto)
                    ->response()
                    ->setStatusCode($statusCode);
            }
        });
    })->create();
