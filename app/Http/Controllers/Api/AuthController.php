<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\ErrorResource;
use App\Http\Resources\Api\MessageResource;
use App\Http\Resources\Api\TokenResource;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Auth',
    description: 'Логин, логаут, обновление токена, текущий пользователь'
)]
class AuthController extends ApiController
{
    /**
     * Логин: email + password → JWT (php-open-source-saver/jwt-auth).
     */
    #[OA\Post(
        path: '/v1/auth/login',
        operationId: 'authLogin',
        tags: ['Auth'],
        summary: 'Вход',
        description: 'Возвращает JWT по email и паролю',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный вход',
                content: new OA\JsonContent(ref: TokenResource::class)
            ),
            new OA\Response(
                response: 401,
                description: 'Неверные учётные данные',
                content: new OA\JsonContent(ref: ErrorResource::class)
            ),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->execute(function () use ($request) {
            $credentials = $request->only('email', 'password');
            $ttlMinutes = (int) config('jwt.ttl');

            if (! $token = auth('api')->attempt($credentials)) {
                return ErrorResource::make([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Неверный email или пароль',
                ])
                    ->response()
                    ->setStatusCode(401);
            }

            return TokenResource::make([
                'token' => $token,
                'expires_in' => $ttlMinutes * 60,
            ])
                ->response()
                ->setStatusCode(200);
        });
    }

    /**
     * Логаут: инвалидация токена (blacklist), клиент отбрасывает токен.
     */
    #[OA\Post(
        path: '/v1/auth/logout',
        operationId: 'authLogout',
        tags: ['Auth'],
        summary: 'Выход',
        description: 'Выход из системы, токен попадает в blacklist',
        security: [['apiAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный выход',
                content: new OA\JsonContent(ref: MessageResource::class)
            ),
        ]
    )]
    public function logout(): JsonResponse
    {
        return $this->execute(function () {
            auth('api')->logout();

            return MessageResource::make([
                'status' => true,
                'code' => 200,
                'msg' => 'Вы вышли',
            ])
                ->response()
                ->setStatusCode(200);
        });
    }

    /**
     * Обновление токена: по текущему JWT выдаётся новый, старый инвалидируется.
     */
    #[OA\Post(
        path: '/v1/auth/refresh',
        operationId: 'authRefresh',
        tags: ['Auth'],
        summary: 'Обновить токен',
        description: 'По текущему JWT выдаётся новый токен',
        security: [['apiAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Новый токен',
                content: new OA\JsonContent(ref: TokenResource::class)
            ),
        ]
    )]
    public function refresh(): JsonResponse
    {
        return $this->execute(function () {
            /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
            $guard = auth('api');
            $token = $guard->refresh();
            $ttlMinutes = (int) config('jwt.ttl');

            return TokenResource::make([
                'token' => $token,
                'expires_in' => $ttlMinutes * 60,
            ])
                ->response()
                ->setStatusCode(200);
        });
    }

    /**
     * Текущий пользователь по JWT.
     */
    #[OA\Get(
        path: '/v1/auth/me',
        operationId: 'authMe',
        tags: ['Auth'],
        summary: 'Текущий пользователь',
        description: 'Возвращает данные аутентифицированного пользователя',
        security: [['apiAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные пользователя',
                content: new OA\JsonContent(ref: UserResource::class)
            ),
        ]
    )]
    public function me(): JsonResponse
    {
        return $this->execute(function () {
            $user = Auth::guard('api')->user();

            return UserResource::make($user)
                ->response()
                ->setStatusCode(200);
        });
    }
}
