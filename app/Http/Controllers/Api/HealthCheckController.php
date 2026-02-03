<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class HealthCheckController
{
    private const DB_TIMEOUT_MS = 5000;

    private const STORAGE_THRESHOLD_PERCENT = 90.0;

    private const MEMORY_LIMIT_MB = 150;

    private const MEMORY_RSS_LIMIT_MB = 300;

    #[OA\Get(
        path: '/health',
        summary: 'Health check endpoint',
        description: 'Проверяет состояние системы: база данных, хранилище, па1мять и системные параметры',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Все проверки прошли успешно',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                        new OA\Property(
                            property: 'checks',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'database', type: 'object'),
                                new OA\Property(property: 'storage', type: 'object'),
                                new OA\Property(property: 'memory_heap', type: 'object'),
                                new OA\Property(property: 'memory_rss', type: 'object'),
                                new OA\Property(property: 'system', type: 'object'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Одна или несколько проверок не прошли',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'checks', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function check(): JsonResponse
    {
        try {
            $checks = [
                'database' => $this->checkDatabase(),
                'storage' => $this->checkStorage(),
                'memory_heap' => $this->checkMemoryHeap(),
                'memory_rss' => $this->checkMemoryRss(),
                'system' => $this->checkSystem(),
            ];

            $overallStatus = $this->getOverallStatus($checks);

            return response()->json([
                'status' => $overallStatus,
                'timestamp' => Carbon::now()->toISOString(),
                'checks' => $checks,
            ], $overallStatus === 'ok' ? 200 : 503);
        } catch (\Throwable $e) {
            // Если что-то пошло не так на уровне контроллера, возвращаем 503
            return response()->json([
                'status' => 'error',
                'timestamp' => Carbon::now()->toISOString(),
                'checks' => [
                    'error' => [
                        'status' => 'down',
                        'details' => 'Health check failed: ' . $e->getMessage(),
                    ],
                ],
            ], 503);
        }
    }

    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $endTime = microtime(true);

            $responseTime = round(($endTime - $startTime) * 1000, 2);

            $connectionName = config('database.default', 'sqlite');
            $connections = config('database.connections', []);
            $driver = $connections[$connectionName]['driver'] ?? 'unknown';

            return [
                'status' => 'up',
                'responseTime' => $responseTime . 'ms',
                'timeout' => self::DB_TIMEOUT_MS . 'ms',
                'details' => ucfirst($driver) . ' connection via Laravel',
            ];
        } catch (\Throwable $e) {
            // Ловим все исключения, включая PDOException и другие
            return [
                'status' => 'down',
                'responseTime' => null,
                'timeout' => self::DB_TIMEOUT_MS . 'ms',
                'details' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $path = storage_path();
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);

            if ($totalSpace === false || $freeSpace === false) {
                throw new \RuntimeException('Unable to get disk space information');
            }

            $usedSpace = $totalSpace - $freeSpace;
            $usedPercent = ($usedSpace / $totalSpace) * 100;

            $status = $usedPercent >= self::STORAGE_THRESHOLD_PERCENT ? 'down' : 'up';

            return [
                'status' => $status,
                'details' => [
                    'path' => $path,
                    'thresholdPercent' => self::STORAGE_THRESHOLD_PERCENT . '%',
                    'totalSpace' => $this->formatBytes($totalSpace),
                    'usedSpace' => $this->formatBytes($usedSpace),
                    'freeSpace' => $this->formatBytes($freeSpace),
                    'usedPercent' => number_format($usedPercent, 2) . '%',
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => [
                    'error' => 'Storage check failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    private function checkMemoryHeap(): array
    {
        try {
            $used = memory_get_usage(true);
            $limit = $this->getMemoryLimit();
            $usagePercent = ($used / $limit) * 100;

            $status = $usagePercent >= 90 ? 'down' : 'up';

            return [
                'status' => $status,
                'details' => [
                    'limit' => $this->formatBytes($limit),
                    'used' => $this->formatBytes($used),
                    'usagePercent' => number_format($usagePercent, 2) . '%',
                    'description' => 'PHP memory usage',
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => [
                    'error' => 'Memory heap check failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    private function checkMemoryRss(): array
    {
        try {
            $used = memory_get_usage(false);
            $peak = memory_get_peak_usage(false);
            $limit = $this->getMemoryLimit() * 2; // RSS обычно больше heap
            $usagePercent = ($used / $limit) * 100;

            $status = $usagePercent >= 90 ? 'down' : 'up';

            return [
                'status' => $status,
                'details' => [
                    'limit' => $this->formatBytes($limit),
                    'used' => $this->formatBytes($used),
                    'peak' => $this->formatBytes($peak),
                    'usagePercent' => number_format($usagePercent, 2) . '%',
                    'description' => 'Process Resident Set Size (total memory)',
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => [
                    'error' => 'Memory RSS check failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    private function checkSystem(): array
    {
        try {
            $platform = PHP_OS_FAMILY;
            $arch = php_uname('m');
            $phpVersion = PHP_VERSION;
            $uptime = $this->getUptime();

            $systemMemory = $this->getSystemMemory();
            $cpuCount = $this->getCpuCount();
            $loadAverage = $this->getLoadAverage();

            return [
                'status' => 'up',
                'details' => [
                    'platform' => strtolower($platform),
                    'arch' => $arch,
                    'phpVersion' => 'v' . $phpVersion,
                    'uptime' => $uptime,
                    'systemMemory' => $systemMemory,
                    'cpuCount' => $cpuCount,
                    'loadAverage' => $loadAverage,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'details' => [
                    'error' => 'System check failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    private function getOverallStatus(array $checks): string
    {
        foreach ($checks as $check) {
            if (isset($check['status']) && $check['status'] === 'down') {
                return 'error';
            }
        }

        return 'ok';
    }

    private function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((float) $bytes, 0);
        if ($bytes == 0) {
            return '0 B';
        }
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return 1024 * 1024 * 1024; // 1GB default if unlimited
        }

        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;

        return match ($last) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptimeContent = @file_get_contents('/proc/uptime');
            if ($uptimeContent !== false) {
                $parts = explode(' ', trim($uptimeContent));
                if (isset($parts[0])) {
                    $uptimeSeconds = (float) $parts[0];
                    $hours = floor($uptimeSeconds / 3600);
                    $minutes = floor(($uptimeSeconds % 3600) / 60);
                    $seconds = floor($uptimeSeconds % 60);

                    return "{$hours}h {$minutes}m {$seconds}s";
                }
            }
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            // macOS
            $output = @shell_exec('sysctl -n kern.boottime');
            if ($output) {
                preg_match('/sec = (\d+)/', $output, $matches);
                if (isset($matches[1])) {
                    $bootTime = (int) $matches[1];
                    $uptimeSeconds = time() - $bootTime;
                    $hours = floor($uptimeSeconds / 3600);
                    $minutes = floor(($uptimeSeconds % 3600) / 60);
                    $seconds = $uptimeSeconds % 60;

                    return "{$hours}h {$minutes}m {$seconds}s";
                }
            }
        }

        // Fallback для других ОС
        return 'N/A';
    }

    private function getSystemMemory(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo !== false) {
                preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $total);
                preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $available);

                if (isset($total[1]) && isset($available[1])) {
                    $totalBytes = (int) $total[1] * 1024;
                    $availableBytes = (int) $available[1] * 1024;
                    $usedBytes = $totalBytes - $availableBytes;
                    $usagePercent = ($usedBytes / $totalBytes) * 100;

                    return [
                        'total' => $this->formatBytes($totalBytes),
                        'used' => $this->formatBytes($usedBytes),
                        'free' => $this->formatBytes($availableBytes),
                        'usagePercent' => number_format($usagePercent, 2) . '%',
                    ];
                }
            }
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            // macOS
            $output = @shell_exec('vm_stat');
            if ($output) {
                // Пытаемся получить информацию о памяти через vm_stat
                // Это сложнее, поэтому просто возвращаем N/A для macOS
                // Можно улучшить, но для healthcheck это не критично
            }
        }

        // Fallback
        return [
            'total' => 'N/A',
            'used' => 'N/A',
            'free' => 'N/A',
            'usagePercent' => 'N/A',
        ];
    }

    private function getCpuCount(): int
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            if ($cpuinfo) {
                return substr_count($cpuinfo, 'processor');
            }
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $output = @shell_exec('sysctl -n hw.ncpu');
            if ($output) {
                return (int) trim($output);
            }
        }

        return 1; // Fallback
    }

    private function getLoadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if ($load !== false) {
                return array_map(fn ($val) => round($val, 2), $load);
            }
        }

        return [0, 0, 0]; // Fallback
    }
}
