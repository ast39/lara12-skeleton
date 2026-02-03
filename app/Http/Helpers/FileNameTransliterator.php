<?php

declare(strict_types=1);

namespace App\Http\Helpers;

use Illuminate\Support\Str;

class FileNameTransliterator
{
    /**
     * Транслитерирует строку, сохраняя дефисы и подчеркивания
     *
     * @param  string  $name  Имя для транслитерации
     * @return string Транслитерированное имя с сохраненными дефисами и подчеркиваниями
     */
    public static function transliteratePreservingDashes(string $name): string
    {
        // Убираем пробелы по краям сначала (чтобы они не превратились в подчеркивания)
        $transliterated = trim($name);

        // Транслитерируем кириллицу и другие не-ASCII символы через ascii
        $transliterated = Str::ascii($transliterated);

        // Заменяем пробелы на подчеркивания
        $transliterated = preg_replace('/\s+/', '_', $transliterated);

        // Удаляем все символы кроме букв, цифр, дефисов и подчеркиваний
        $transliterated = preg_replace('/[^a-zA-Z0-9\-_]/', '', $transliterated);

        // Убираем множественные подчеркивания (но не дефисы)
        $transliterated = preg_replace('/_+/', '_', $transliterated);

        return $transliterated;
    }
}
