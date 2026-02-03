<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResource',
    title: 'Ошибка',
    description: 'Ответ с информацией об ошибке',
    properties: [
        new OA\Property(
            property: 'status',
            type: 'boolean',
            title: 'Статус',
            description: 'Статус операции',
            example: false
        ),
        new OA\Property(
            property: 'code',
            type: 'integer',
            format: 'int64',
            title: 'Код',
            description: 'Код ошибки',
            example: 500
        ),
        new OA\Property(
            property: 'msg',
            type: 'string',
            title: 'Сообщение',
            description: 'Текст сообщения об ошибке',
            example: 'Внутренняя ошибка сервера'
        ),
    ]
)]
class ErrorResource extends JsonResource
{
    public static $wrap = 'error';

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Если это объект с полями status, code, message
        if (
            is_object($this->resource)
            && isset($this->resource->status, $this->resource->code, $this->resource->message)
        ) {
            return [
                'status' => $this->resource->status,
                'code' => $this->resource->code,
                'msg' => $this->resource->message,
            ];
        }

        // Если это массив с полями status, code, message
        if (
            is_array($this->resource)
            && isset($this->resource['status'], $this->resource['code'], $this->resource['message'])
        ) {
            return [
                'status' => $this->resource['status'],
                'code' => $this->resource['code'],
                'msg' => $this->resource['message'],
            ];
        }

        // Иначе используем стандартные поля
        return [
            'status' => $this->status ?? false,
            'code' => $this->code ?? 500,
            'msg' => $this->msg ?? null,
        ];
    }

    public function jsonOptions()
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
