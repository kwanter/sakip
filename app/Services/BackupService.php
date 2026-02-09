<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

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
     * Backup MySQL/MariaDB database
     */
    protected function backupMySQL(array $config, string $backupPath, string $filename): array
    {
        $host = escapeshellarg($config['host']);
        $port = escapeshellarg($config['port']);
        $database = escapeshellarg($config['database']);
        $username = escapeshellarg($config['username']);
        $password = $config['password'];

        $backupFile = escapeshellarg($backupPath . '/' . $filename . '.sql');

        // SECURITY: Use MYSQL_PWD environment variable instead of --password flag
        // The --password flag exposes the password in the process list (ps aux)
        // Using MYSQL_PWD environment variable keeps it out of the process list
        if (!empty($password)) {
            $sanitizedPassword = addslashes($password);
            putenv("MYSQL_PWD={$sanitizedPassword}");
        }

        $command = "mysqldump --host={$host} --port={$port} --user={$username} --single-transaction --routines --triggers {$database} > {$backupFile}";

        exec($command, $output, $returnCode);

        // Clear the password from environment after command execution
        if (!empty($password)) {
            putenv('MYSQL_PWD');
        }

        if ($returnCode !== 0) {
            Log::error('MySQL backup failed', ['return_code' => $returnCode]);
            throw new \Exception(
                'Failed to run mysqldump. Ensure mysqldump is installed and credentials are correct.'
            );
        }

        $actualBackupFile = $backupPath . '/' . $filename . '.sql';
        return [
            'filename' => $filename . '.sql',
            'path' => $actualBackupFile,
            'size' => $this->formatBytes(filesize($actualBackupFile)),
        ];
    }

    /**
     * Backup PostgreSQL database
     */
    protected function backupPostgreSQL(array $config, string $backupPath, string $filename): array
    {
        $host = escapeshellarg($config['host']);
        $port = escapeshellarg($config['port']);
        $database = escapeshellarg($config['database']);
        $username = escapeshellarg($config['username']);
        $password = $config['password'];

        $backupFile = escapeshellarg($backupPath . '/' . $filename . '.sql');

        // SECURITY: Use PGPASSWORD environment variable
        if (!empty($password)) {
            $sanitizedPassword = addslashes($password);
            putenv("PGPASSWORD={$sanitizedPassword}");
        }

        $command = "pg_dump --host={$host} --port={$port} --username={$username} --format=plain --no-owner --no-acl {$database} > {$backupFile}";

        exec($command, $output, $returnCode);

        // Clear the password from environment
        if (!empty($password)) {
            putenv('PGPASSWORD');
        }

        if ($returnCode !== 0) {
            Log::error('PostgreSQL backup failed', ['return_code' => $returnCode]);
            throw new \Exception(
                'Failed to run pg_dump. Ensure PostgreSQL client is installed and credentials are correct.'
            );
        }

        $actualBackupFile = $backupPath . '/' . $filename . '.sql';
        return [
            'filename' => $filename . '.sql',
            'path' => $actualBackupFile,
            'size' => $this->formatBytes(filesize($actualBackupFile)),
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
        $host = escapeshellarg($config['host']);
        $database = escapeshellarg($config['database']);
        $username = escapeshellarg($config['username']);
        $password = $config['password'];

        $backupFile = $backupPath . '/' . $filename . '.bak';
        $escapedBackupFile = escapeshellarg($backupFile);

        // SECURITY: Use SQLCMDPASSWORD environment variable to prevent exposure in process list
        if (!empty($password)) {
            $sanitizedPassword = addslashes($password);
            putenv("SQLCMDPASSWORD={$sanitizedPassword}");
        }

        $command = "sqlcmd -S {$host} -U {$username} -Q \"BACKUP DATABASE [{$database}] TO DISK = '{$escapedBackupFile}'\"";

        exec($command, $output, $returnCode);

        // Clear the password from environment after command execution
        if (!empty($password)) {
            putenv('SQLCMDPASSWORD');
        }

        if ($returnCode !== 0) {
            Log::error('SQL Server backup failed', ['return_code' => $returnCode]);
            throw new \Exception(
                'Failed to run SQL Server backup. Ensure sqlcmd is installed and credentials are correct.'
            );
        }

        return [
            'filename' => $filename . '.bak',
            'path' => $backupFile,
            'size' => $this->formatBytes(filesize($backupFile)),
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
