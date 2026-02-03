<?php

namespace App\Scopes\Api;

use App\Scopes\Filter\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class TestScope extends AbstractFilter
{
    public const QUERY = 'query';

    /**
     * @return array[]
     */
    protected function getCallbacks(): array
    {
        return [
            self::QUERY => [$this, 'query'],
        ];
    }

    public function query(Builder $builder, $value): void
    {
        $builder->where('title', 'like', '%' . $value . '%')
            ->orWhere('description', 'like', '%' . $value . '%');
    }
}
