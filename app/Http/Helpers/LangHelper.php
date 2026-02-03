<?php

namespace App\Http\Helpers;

use Illuminate\Support\Str;

class LangHelper
{
    public static function getAppMode(string $section): string
    {
        $categoryNames = [
            'dev' => 'Разработка',
            'prod' => 'Продакшн',
        ];

        return $categoryNames[$section] ?? Str::title($section);
    }
}
