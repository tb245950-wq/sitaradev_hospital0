<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DailyDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'db:backup-daily';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Melakukan backup database PostgreSQL harian dengan kompresi gzip';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai proses backup database...');
        
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql.gz";
        $backupDir = storage_path('app/backups');
        $filePath = "{$backupDir}/{$filename}";

        // Pastikan direktori ada
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $startedAt = now();
        
        // Konfigurasi Database
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        // Command pg_dump
        // Menggunakan PGPASSWORD environment variable untuk keamanan non-interaktif
        $command = sprintf(
            "PGPASSWORD='%s' pg_dump -h %s -p %s -U %s %s | gzip > %s",
            $password, $host, $port, $username, $database, $filePath
        );

        $output = [];
        $returnVar = null;
        
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $size = filesize($filePath);
            $this->info("✅ Backup berhasil: {$filename} ({$size} bytes)");
            
            // Log ke Database
            DB::table('backup_logs')->insert([
                'filename' => $filename,
                'status' => 'success',
                'size_bytes' => $size,
                'started_at' => $startedAt,
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Hapus backup lama (> 7 hari)
            $this->cleanupOldBackups();
            
            return 0;
        } else {
            $errorMsg = "Gagal mengeksekusi pg_dump. Return code: {$returnVar}";
            $this->error("❌ {$errorMsg}");
            
            DB::table('backup_logs')->insert([
                'filename' => $filename,
                'status' => 'failed',
                'error_message' => $errorMsg,
                'started_at' => $startedAt,
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return 1;
        }
    }

    /**
     * Menghapus file backup yang lebih tua dari 7 hari.
     */
    private function cleanupOldBackups()
    {
        $files = glob(storage_path('app/backups/*.sql.gz'));
        $now = time();
        $retentionDays = 7;

        foreach ($files as $file) {
            if ($now - filemtime($file) >= ($retentionDays * 24 * 60 * 60)) {
                unlink($file);
                $this->line("🗑️  Backup lama dihapus: " . basename($file));
            }
        }
    }
}
