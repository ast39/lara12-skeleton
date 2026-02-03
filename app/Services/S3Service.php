<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\StorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service implements StorageServiceInterface
{
    protected string $bucket;

    protected string $disk;

    public function __construct()
    {
        $this->disk = 'documents';
        $this->bucket = config('filesystems.disks.documents.bucket');
    }

    /**
     * Загрузить файл в S3
     */
    public function upload(UploadedFile $file, string $path = '', ?string $filename = null): string
    {
        $filename = $filename ?: $file->getClientOriginalName();
        $fullPath = $this->buildPath($path, $filename);

        $uploaded = Storage::disk($this->disk)->putFileAs(
            $path,
            $file,
            $filename
        );

        if (! $uploaded) {
            throw new \Exception('Не удалось загрузить файл в S3');
        }

        return $fullPath;
    }

    /**
     * Удалить файл из S3
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Проверить существование файла в S3
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Получить URL файла из S3
     */
    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Получить содержимое файла из S3
     */
    public function get(string $path): string
    {
        return Storage::disk($this->disk)->get($path);
    }

    /**
     * Получить размер файла из S3
     */
    public function size(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    /**
     * Получить MIME тип файла из S3
     */
    public function mimeType(string $path): string
    {
        return Storage::disk($this->disk)->mimeType($path);
    }

    /**
     * Получить список файлов в директории S3
     */
    public function files(string $directory = ''): Collection
    {
        try {
            $files = Storage::disk($this->disk)->files($directory);

            return collect($files)->map(function ($file) {
                return [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => $this->size($file),
                    'mime_type' => $this->mimeType($file),
                    'url' => $this->url($file),
                    'last_modified' => Storage::disk($this->disk)->lastModified($file),
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Ошибка получения списка файлов S3: ' . $e->getMessage());

            return collect();
        }
    }

    /**
     * Получить список директорий в S3
     */
    public function directories(string $directory = ''): Collection
    {
        $directories = Storage::disk($this->disk)->directories($directory);

        return collect($directories)->map(function ($dir) {
            return [
                'path' => $dir,
                'name' => basename($dir),
                'files_count' => count($this->files($dir)),
            ];
        });
    }

    /**
     * Создать директорию в S3
     */
    public function makeDirectory(string $path): bool
    {
        // В S3 директории создаются автоматически при загрузке файлов
        // Но можно создать пустой файл-маркер
        $markerPath = rtrim($path, '/') . '/.gitkeep';

        return Storage::disk($this->disk)->put($markerPath, '');
    }

    /**
     * Удалить директорию из S3
     */
    public function deleteDirectory(string $path): bool
    {
        $files = Storage::disk($this->disk)->allFiles($path);

        if (empty($files)) {
            return true;
        }

        return Storage::disk($this->disk)->delete($files);
    }

    /**
     * Скопировать файл в S3
     */
    public function copy(string $from, string $to): bool
    {
        return Storage::disk($this->disk)->copy($from, $to);
    }

    /**
     * Переместить файл в S3
     */
    public function move(string $from, string $to): bool
    {
        return Storage::disk($this->disk)->move($from, $to);
    }

    /**
     * Получить временную ссылку для скачивания
     */
    public function temporaryUrl(string $path, int $minutes = 60): string
    {
        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($minutes));
    }

    /**
     * Получить пресет URL для CloudFront (если используется)
     */
    public function cloudFrontUrl(string $path): string
    {
        $cloudFrontDomain = config('filesystems.disks.s3.cloudfront_domain');

        if ($cloudFrontDomain) {
            return rtrim($cloudFrontDomain, '/') . '/' . ltrim($path, '/');
        }

        return $this->url($path);
    }

    /**
     * Получить статистику по хранилищу
     */
    public function getStorageStats(): array
    {
        try {
            $allFiles = Storage::disk($this->disk)->allFiles();
            $totalSize = 0;
            $fileCount = count($allFiles);

            foreach ($allFiles as $file) {
                try {
                    $totalSize += $this->size($file);
                } catch (\Exception $e) {
                    \Log::warning('Не удалось получить размер файла: ' . $file);
                }
            }

            return [
                'total_files' => $fileCount,
                'total_size' => $totalSize,
                'total_size_formatted' => $this->formatBytes($totalSize),
                'bucket' => $this->bucket,
            ];
        } catch (\Exception $e) {
            \Log::error('Ошибка получения статистики S3: ' . $e->getMessage());

            return [
                'total_files' => 0,
                'total_size' => 0,
                'total_size_formatted' => '0 B',
                'bucket' => $this->bucket,
            ];
        }
    }

    /**
     * Поиск файлов по имени
     */
    public function searchFiles(string $query, string $directory = ''): Collection
    {
        $allFiles = Storage::disk($this->disk)->allFiles($directory);

        return collect($allFiles)->filter(function ($file) use ($query) {
            return Str::contains(strtolower(basename($file)), strtolower($query));
        })->map(function ($file) {
            return [
                'path' => $file,
                'name' => basename($file),
                'size' => $this->size($file),
                'mime_type' => $this->mimeType($file),
                'url' => $this->url($file),
                'last_modified' => Storage::disk($this->disk)->lastModified($file),
            ];
        });
    }

    /**
     * Построить полный путь к файлу
     */
    protected function buildPath(string $path, string $filename): string
    {
        $path = trim($path, '/');
        $filename = basename($filename);

        return $path ? $path . '/' . $filename : $filename;
    }

    /**
     * Форматировать размер файла в читаемый вид
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
