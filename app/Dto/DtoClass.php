<?php

namespace App\Dto;

class DtoClass
{
    public ?int $page = null;

    public ?int $limit = null;

    public ?string $order = null;

    public ?string $reverse = null;

    protected array $nullableFields = [];

    public function __construct(array $data, array $nullableFields = [])
    {
        $this->nullableFields = $nullableFields;

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function toArray(): array
    {
        return collect($this)
            ->filter(function ($value, $key) {
                return ! is_null($value) || in_array($key, $this->nullableFields, true);
            })
            ->map(function ($value, $key) {
                return in_array($key, $this->nullableFields, true) && is_null($value)
                    ? ''
                    : $value;
            })
            ->toArray();
    }
}
