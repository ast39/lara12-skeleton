<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

class AboutController
{
    /**
     * Получение информации о проекте из README.md
     */
    public function index(): Response
    {
        try {
            $readmePath = base_path('README.md');

            if (! File::exists($readmePath)) {
                return response()->view('about', [
                    'error' => 'README.md файл не найден',
                    'readme_content' => '',
                    'readme_html' => '',
                ], 404);
            }

            $readmeContent = File::get($readmePath);

            // Конвертируем Markdown в HTML
            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $readmeHtml = $converter->convert($readmeContent);

            return response()->view('about', [
                'readme_content' => $readmeContent,
                'readme_html' => $readmeHtml,
                'project_name' => config('services.project_name'),
                'description' => config('services.description'),
            ]);
        } catch (\Exception $e) {
            return response()->view('about', [
                'error' => 'Ошибка при чтении README.md: ' . $e->getMessage(),
                'readme_content' => '',
                'readme_html' => '',
            ], 500);
        }
    }

    /**
     * Вывод содержимого PRE_COMMIT.md (инструменты и команды перед коммитом)
     */
    public function preCommit(): Response
    {
        try {
            $path = base_path('PRE_COMMIT.md');

            if (! File::exists($path)) {
                return response()->view('pre-commit', [
                    'error' => 'PRE_COMMIT.md не найден',
                    'pre_commit_html' => '',
                ], 404);
            }

            $content = File::get($path);
            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $html = $converter->convert($content);

            return response()->view('pre-commit', [
                'pre_commit_html' => $html,
                'project_name' => config('services.project_name'),
            ]);
        } catch (\Exception $e) {
            return response()->view('pre-commit', [
                'error' => 'Ошибка при чтении PRE_COMMIT.md: ' . $e->getMessage(),
                'pre_commit_html' => '',
            ], 500);
        }
    }
}
