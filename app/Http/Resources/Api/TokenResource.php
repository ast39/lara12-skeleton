<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TokenResource',
    title: 'Токен авторизации',
    description: 'JWT и время жизни в секундах',
    properties: [
        new OA\Property(
            property: 'token',
            type: 'string',
            description: 'JWT',
            example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'
        ),
        new OA\Property(
            property: 'expires_in',
            type: 'integer',
            description: 'Время жизни токена в секундах',
            example: 3600
        ),
    ]
)]
class TokenResource extends JsonResource
{
    public static $wrap = 'data';

    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource['token'],
            'expires_in' => $this->resource['expires_in'],
        ];
    }

    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
