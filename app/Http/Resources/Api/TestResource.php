<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\ApiResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TestResource',
    title: 'Тест',
    description: 'Информация о тесте',
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64', title: 'ID', description: 'Уникальный идентификатор теста', example: 1),
        new OA\Property(property: 'title', type: 'string', title: 'Название', description: 'Наименование теста', example: 'Тест 1'),
        new OA\Property(property: 'description', type: 'string', title: 'Описание', description: 'Описание теста', example: 'Описание теста'),
    ]
)]
class TestResource extends ApiResource
{
    public static $wrap = 'data';

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title'  => $this->resource->title,
            'description'  => $this->resource->description,
        ];
    }
}
