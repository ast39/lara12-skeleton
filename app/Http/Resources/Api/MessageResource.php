<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResource',
    title: 'Простое сообщение',
    description: 'Ответ с простым сообщением',
    properties: [
        new OA\Property(
            property: 'status',
            type: 'boolean',
            title: 'Статус',
            description: 'Статус операции',
            example: true
        ),
        new OA\Property(
            property: 'code',
            type: 'integer',
            format: 'int64',
            title: 'Код',
            description: 'Код ответа',
            example: 200
        ),
        new OA\Property(
            property: 'msg',
            type: 'string',
            title: 'Сообщение',
            description: 'Текст сообщения',
            example: 'Test'
        ),
    ]
)]
class MessageResource extends JsonResource
{
    public static $wrap = 'data';

    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource['status'] ?? true,
            'code' => $this->resource['code'] ?? 200,
            'msg' => $this->resource['msg'] ?? 'Test',
        ];
    }

    public function jsonOptions()
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
