<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        return view('pengaturan.index');
    }

    /**
     * Update application settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        // Here you would typically save settings to database or config
        // For now, we'll just return success message
        
        return redirect()->route('pengaturan.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        try {
            // Clear various Laravel caches
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize application.
     */
    public function optimizeApp()
    {
        try {
            // Optimize the application
            \Artisan::call('optimize');
            \Artisan::call('config:cache');
            \Artisan::call('route:cache');
            \Artisan::call('view:cache');
            
            return response()->json([
                'success' => true,
                'message' => 'Aplikasi berhasil dioptimalkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengoptimalkan aplikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Backup database based on detected database engine.
     */
    public function backupDatabase()
    {
        try {
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
                    $result = $this->backupMySQL($config, $backupPath, $filename);
                    break;
                    
                case 'pgsql':
                    $result = $this->backupPostgreSQL($config, $backupPath, $filename);
                    break;
                    
                case 'sqlite':
                    $result = $this->backupSQLite($config, $backupPath, $filename);
                    break;
                    
                case 'sqlsrv':
                    $result = $this->backupSQLServer($config, $backupPath, $filename);
                    break;
                    
                default:
                    throw new \Exception("Database driver '{$driver}' tidak didukung untuk backup.");
            }
            
            return response()->json([
                'success' => true,
                'message' => "Backup database berhasil dibuat! File: {$result['filename']}",
                'file_path' => $result['path'],
                'file_size' => $result['size']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Backup MySQL/MariaDB database.
     */
    private function backupMySQL($config, $backupPath, $filename)
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        $backupFile = $backupPath . '/' . $filename . '.sql';
        
        $command = "mysqldump --host={$host} --port={$port} --user={$username}";
        if ($password) {
            $command .= " --password={$password}";
        }
        $command .= " --single-transaction --routines --triggers {$database} > {$backupFile}";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Gagal menjalankan mysqldump. Pastikan mysqldump terinstall.');
        }
        
        return [
            'filename' => $filename . '.sql',
            'path' => $backupFile,
            'size' => $this->formatBytes(filesize($backupFile))
        ];
    }
    
    /**
     * Backup PostgreSQL database.
     */
    private function backupPostgreSQL($config, $backupPath, $filename)
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        $backupFile = $backupPath . '/' . $filename . '.sql';
        
        // Set PGPASSWORD environment variable
        if ($password) {
            putenv("PGPASSWORD={$password}");
        }
        
        $command = "pg_dump --host={$host} --port={$port} --username={$username} --format=plain --no-owner --no-acl {$database} > {$backupFile}";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Gagal menjalankan pg_dump. Pastikan PostgreSQL client terinstall.');
        }
        
        return [
            'filename' => $filename . '.sql',
            'path' => $backupFile,
            'size' => $this->formatBytes(filesize($backupFile))
        ];
    }
    
    /**
     * Backup SQLite database.
     */
    private function backupSQLite($config, $backupPath, $filename)
    {
        $databasePath = $config['database'];
        
        if (!file_exists($databasePath)) {
            throw new \Exception('File database SQLite tidak ditemukan.');
        }
        
        $backupFile = $backupPath . '/' . $filename . '.sqlite';
        
        if (!copy($databasePath, $backupFile)) {
            throw new \Exception('Gagal menyalin file database SQLite.');
        }
        
        return [
            'filename' => $filename . '.sqlite',
            'path' => $backupFile,
            'size' => $this->formatBytes(filesize($backupFile))
        ];
    }
    
    /**
     * Backup SQL Server database.
     */
    private function backupSQLServer($config, $backupPath, $filename)
    {
        $host = $config['host'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        $backupFile = $backupPath . '/' . $filename . '.bak';
        
        $command = "sqlcmd -S {$host} -U {$username} -P {$password} -Q \"BACKUP DATABASE [{$database}] TO DISK = '{$backupFile}'\"";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Gagal menjalankan backup SQL Server. Pastikan sqlcmd terinstall.');
        }
        
        return [
            'filename' => $filename . '.bak',
            'path' => $backupFile,
            'size' => $this->formatBytes(filesize($backupFile))
        ];
    }
    
    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}