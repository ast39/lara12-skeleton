<?php

namespace App\Http\Controllers\Api;

use App\Dto\ServerErrorDto;
use App\Http\Resources\Api\ErrorResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// php artisan l5-swagger:generate

#[OA\Info(
    version: '1.0.0',
    title: 'Rest Api [Cometa Aspro Sync]',
    description: 'Rest Api by Laravel for Cometa Aspro Sync',
    contact: new OA\Contact(
        email: 'alexandr.statut@gmail.com',
        name: 'ASt'
    )
)]
#[OA\Server(
    url: L5_SWAGGER_DEV_HOST,
    description: 'Локальный API сервер v1'
)]
#[OA\Server(
    url: L5_SWAGGER_PROD_HOST,
    description: 'Продакшен API сервер v1'
)]
#[OA\SecurityScheme(
    type: 'apiKey',
    description: 'API ключ для аутентификации. Передать через заголовок X-API-Key, '
        . 'Authorization Bearer или HTTP Only Cookie. В Swagger UI — заголовок X-API-Key.',
    name: 'X-API-Key',
    in: 'header',
    securityScheme: 'apiAuth'
)]
#[OA\Tag(
    name: 'Api',
    description: 'Основной API'
)]
#[OA\Tag(
    name: 'Health',
    description: 'Health'
)]
#[OA\Tag(
    name: 'Auth',
    description: 'Auth'
)]
#[OA\Tag(
    name: 'Test',
    description: 'Test'
)]
abstract class ApiController
{
    use AuthorizesRequests;

    /**
     * Общий интерфейс выполнения эндпоинтов через try catch с логированием и возвратом ошибок
     */
    protected function execute(callable $callback, bool $useTransaction = false): JsonResponse|Response
    {
        try {
            if ($useTransaction) {
                DB::beginTransaction();
            }

            $response = $callback();

            if ($useTransaction) {
                DB::commit();
            }

            return $response;
        } catch (\Exception $e) {
            if ($useTransaction) {
                DB::rollBack();
            }

            // Здесь не нужно использовать ??, просто передаем $e->getFile()
            Log::error($e->getFile(), ['msg' => $e->getMessage()]);

            $statusCode = $e->getCode();
            // Проверяем что код является валидным HTTP статусом (100-599)
            if (! is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
                $statusCode = 500;
            }

            return ErrorResource::make(new ServerErrorDto($e->getMessage(), $statusCode))
                ->response()
                ->setStatusCode($statusCode);
        }
    }
}
