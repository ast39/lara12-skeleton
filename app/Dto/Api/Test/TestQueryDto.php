<?php

namespace App\Dto\Api\Test;

use App\Dto\DtoClass;

class TestQueryDto extends DtoClass
{
    public ?string $query = null;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->query = $data['query'] ?? null;
    }
}
