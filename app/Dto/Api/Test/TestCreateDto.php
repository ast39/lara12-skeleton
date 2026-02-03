<?php

namespace App\Dto\Api\Test;

use App\Dto\DtoClass;

class TestCreateDto extends DtoClass
{
    public string $title;

    public ?string $description;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->title = $data['title'];
        $this->description = $data['description'] ?? null;
    }
}
