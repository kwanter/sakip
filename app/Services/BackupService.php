<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupService
{
    /**
     * Create database backup based on detected database engine
     *
     * @return array Array with filename, path, and size
     * @throws \Exception
     */
    public function createBackup(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $driver = $config['driver'];

        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}";

        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                return $this->backupMySQL($config, $backupPath, $filename);
            case 'pgsql':
                return $this->backupPostgreSQL($config, $backupPath, $filename);
            case 'sqlite':
                return $this->backupSQLite($config, $backupPath, $filename);
            case 'sqlsrv':
                return $this->backupSQLServer($config, $backupPath, $filename);
            default:
                throw new \Exception("Database driver '{$driver}' not supported for backup.");
        }
    }

    /**
     * Run a process with optional env vars and write stdout to a file.
     *
     * @throws \Exception
     */
    private static function runBackupCmd(array $cmd, array $env, string $outFile): void
    {
        $process = new Process($cmd, null, $env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        file_put_contents($outFile, $process->getOutput());
    }

/**
     * Backup MySQL/MariaDB database
     */
    protected function backupMySQL(array $config, string $backupPath, string $filename): array
    {
        $outFile = $backupPath . '/' . $filename . '.sql';

        $pwd = $config['password'] ?? '';
        $env = $pwd !== '' ? ['MYSQL_PWD' => $pwd] : [];

        self::runBackupCmd(
            [
                'mysqldump',
                '--host=' . $config['host'],
                '--port=' . (string) $config['port'],
                '--user=' . $config['username'],
                '--single-transaction',
                '--routines',
                '--triggers',
                $config['database'],
            ],
            $env,
            $outFile,
        );

        return [
            'filename' => $filename . '.sql',
            'path' => $outFile,
            'size' => $this->formatBytes(filesize($outFile)),
        ];
    }

    /**
     * Backup PostgreSQL database
     */
    protected function backupPostgreSQL(array $config, string $backupPath, string $filename): array
    {
        $outFile = $backupPath . '/' . $filename . '.sql';

        $pwd = $config['password'] ?? '';
        $env = $pwd !== '' ? ['PGPASSWORD' => $pwd] : [];

        self::runBackupCmd(
            [
                'pg_dump',
                '--host=' . $config['host'],
                '--port=' . (string) $config['port'],
                '--username=' . $config['username'],
                '--format=plain',
                '--no-owner',
                '--no-acl',
                $config['database'],
            ],
            $env,
            $outFile,
        );

        return [
            'filename' => $filename . '.sql',
            'path' => $outFile,
            'size' => $this->formatBytes(filesize($outFile)),
        ];
    }

    /**
     * Backup SQLite database
     */
    protected function backupSQLite(array $config, string $backupPath, string $filename): array
    {
        $databasePath = $config['database'];
        if (!file_exists($databasePath)) {
            throw new \Exception('SQLite database file not found.');
        }

        $backupFile = $backupPath . '/' . $filename . '.sqlite';
        if (!copy($databasePath, $backupFile)) {
            throw new \Exception('Failed to copy SQLite database file.');
        }

        return [
            'filename' => $filename . '.sqlite',
            'path' => $backupFile,
            'size' => $this->formatBytes(filesize($backupFile)),
        ];
    }

    /**
     * Backup SQL Server database
     */
    protected function backupSQLServer(array $config, string $backupPath, string $filename): array
    {
        $outFile = $backupPath . '/' . $filename . '.bak';

        $pwd = $config['password'] ?? '';
        $env = $pwd !== '' ? ['SQLCMDPASSWORD' => $pwd] : [];

        self::runBackupCmd(
            [
                'sqlcmd',
                '-S', $config['host'],
                '-U', $config['username'],
                '-Q', "BACKUP DATABASE [{$config['database']}] TO DISK = N'" . $outFile . "'",
            ],
            $env,
            $outFile,
        );

        return [
            'filename' => $filename . '.bak',
            'path' => $outFile,
            'size' => $this->formatBytes(filesize($outFile)),
        ];
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
