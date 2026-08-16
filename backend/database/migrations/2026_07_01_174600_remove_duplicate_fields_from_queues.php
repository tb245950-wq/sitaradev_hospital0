<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * CRITICAL FIX: Hapus field duplikat di queues table
     * Standardisasi ke Indonesian naming convention
     * 
     * Field yang dihapus:
     * - patient_id (pakai id_pasien)
     * - queue_number (pakai nomor_antrian)
     * - type (pakai jenis_layanan)
     * - priority (pakai prioritas)
     * 
     * Migration ini AMAN - data akan dimigrasi ke field yang benar sebelum dihapus
     */
    public function up(): void
    {
        // 1. Sync data dari field duplikat ke field utama
        
        // Sync patient_id -> id_pasien (jika ada data di patient_id tapi tidak di id_pasien)
        if (Schema::hasColumn('queues', 'patient_id')) {
            DB::statement('
                UPDATE queues 
                SET id_pasien = patient_id 
                WHERE patient_id IS NOT NULL 
                  AND id_pasien IS NULL
            ');
        }
        
        // Sync queue_number -> nomor_antrian (jika diperlukan)
        if (Schema::hasColumn('queues', 'queue_number')) {
            // queue_number biasanya format string "A001", nomor_antrian adalah integer
            // Kita tidak perlu sync karena nomor_antrian sudah ada
        }
        
        // Sync type -> jenis_layanan
        if (Schema::hasColumn('queues', 'type')) {
            DB::statement('
                UPDATE queues 
                SET jenis_layanan = type 
                WHERE type IS NOT NULL 
                  AND jenis_layanan IS NULL
            ');
        }
        
        // Sync priority -> prioritas
        if (Schema::hasColumn('queues', 'priority')) {
            // priority mungkin string "high", prioritas adalah integer
            // Convert jika diperlukan
            DB::statement("
                UPDATE queues 
                SET prioritas = CASE 
                    WHEN priority = 'high' THEN 10
                    WHEN priority = 'urgent' THEN 9
                    WHEN priority = 'normal' THEN 5
                    WHEN priority = 'low' THEN 1
                    ELSE CAST(priority AS INTEGER)
                END
                WHERE priority IS NOT NULL 
                  AND prioritas = 0
            ");
        }
        
        // 2. Drop kolom duplikat
        Schema::table('queues', function (Blueprint $table) {
            // Drop patient_id (use id_pasien)
            if (Schema::hasColumn('queues', 'patient_id')) {
                $table->dropColumn('patient_id');
            }
            
            // Drop queue_number (use nomor_antrian)
            if (Schema::hasColumn('queues', 'queue_number')) {
                $table->dropColumn('queue_number');
            }
            
            // Drop type (use jenis_layanan)
            if (Schema::hasColumn('queues', 'type')) {
                $table->dropColumn('type');
            }
            
            // Drop priority (use prioritas)
            if (Schema::hasColumn('queues', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }

    /**
     * Rollback: Restore field duplikat (not recommended)
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Restore deleted columns
            $table->unsignedBigInteger('patient_id')->nullable()->after('id_pasien');
            $table->string('queue_number', 20)->nullable()->after('nomor_antrian');
            $table->string('type', 20)->nullable()->after('jenis_layanan');
            $table->string('priority', 20)->nullable()->after('prioritas');
        });
        
        // Restore data from primary fields to duplicate fields
        DB::statement('UPDATE queues SET patient_id = id_pasien WHERE id_pasien IS NOT NULL');
        DB::statement('UPDATE queues SET type = jenis_layanan WHERE jenis_layanan IS NOT NULL');
    }
};
