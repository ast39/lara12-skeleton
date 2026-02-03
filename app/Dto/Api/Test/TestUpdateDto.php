<?php

namespace App\Dto\Api\Test;

use App\Dto\DtoClass;

class TestUpdateDto extends DtoClass
{
    public int $id;

    public string $title;

    public ?string $description;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->id = $data['id'];

        if (array_key_exists('title', $data)) {
            $this->title = $data['title'];
        }
        $this->description = $data['description'] ?? null;
    }
}
