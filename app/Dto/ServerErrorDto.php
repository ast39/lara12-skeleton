<?php

namespace App\Dto;

class ServerErrorDto
{
    public bool $status = false;

    public int $code = 500;

    public ?string $message;

    public function __construct(?string $message = null, int|string $code = 500)
    {
        $this->message = $message ?? __('error.global.500');
        $this->code = (int) $code;
    }
}
