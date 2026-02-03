<?php

namespace App\Exceptions\Api;

class TestException extends \Exception
{
    /**
     * @var mixed
     */
    protected $message;

    /**
     * @var int|mixed
     */
    protected $code = 400;

    public function __construct($message = null, $code = null)
    {
        if ($message) {
            $this->message = $message;
        }
        if ($code) {
            $this->code = $code;
        }

        parent::__construct($this->message, $this->code);
    }

    /**
     * Исключение - Тест не найден
     */
    public static function notFound(): self
    {
        return new self('Тест не найден', 404);
    }

    /**
     * Исключение - Дубль тест
     */
    public static function doubleTest(): self
    {
        return new self('Тест с таким названием уже существует', 400);
    }
}
