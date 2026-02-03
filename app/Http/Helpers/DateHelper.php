<?php

namespace App\Http\Helpers;

class DateHelper
{
    public static function getDay(string $date): string
    {
        return date('d', strtotime($date));
    }

    public static function getMonth(string $date): string
    {
        return date('m', strtotime($date));
    }

    public static function getYear(string $date): string
    {
        return date('Y', strtotime($date));
    }
}
