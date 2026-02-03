<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface StorageServiceInterface
{
    /**
     * Загрузить файл в хранилище
     */
    public function upload(UploadedFile $file, string $path = '', ?string $filename = null): string;

    /**
     * Удалить файл из хранилища
     */
    public function delete(string $path): bool;

    /**
     * Проверить существование файла
     */
    public function exists(string $path): bool;

    /**
     * Получить URL файла
     */
    public function url(string $path): string;

    /**
     * Получить содержимое файла
     */
    public function get(string $path): string;

    /**
     * Получить размер файла
     */
    public function size(string $path): int;

    /**
     * Получить MIME тип файла
     */
    public function mimeType(string $path): string;

    /**
     * Получить список файлов в директории
     */
    public function files(string $directory = ''): Collection;

    /**
     * Получить список директорий
     */
    public function directories(string $directory = ''): Collection;

    /**
     * Создать директорию
     */
    public function makeDirectory(string $path): bool;

    /**
     * Удалить директорию
     */
    public function deleteDirectory(string $path): bool;

    /**
     * Скопировать файл
     */
    public function copy(string $from, string $to): bool;

    /**
     * Переместить файл
     */
    public function move(string $from, string $to): bool;
}
