<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * CRITICAL FIX: Foreign key patient_id harus menunjuk ke patients, bukan users
     * Migration ini AMAN - tidak akan menghapus data
     * 
     * Step:
     * 1. Drop foreign key constraint yang salah (patient_id -> users)
     * 2. Clean up data yang tidak valid
     * 3. Sync patient_id dari id_pasien
     * 4. Buat foreign key yang benar (patient_id -> patients)
     */
    public function up(): void
    {
        // Check if patient_id column exists
        if (!Schema::hasColumn('queues', 'patient_id')) {
            // Kolom patient_id belum ada, skip migration
            return;
        }

        // Step 1: Drop existing foreign key constraint (patient_id -> users)
        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });
        
        // Step 2: Set patient_id = NULL untuk data yang tidak valid
        DB::statement('
            UPDATE queues 
            SET patient_id = NULL 
            WHERE patient_id IS NOT NULL 
              AND patient_id NOT IN (SELECT id_pasien FROM patients)
        ');
        
        // Step 3: Sync patient_id dari id_pasien untuk data yang valid
        DB::statement('
            UPDATE queues 
            SET patient_id = id_pasien 
            WHERE id_pasien IS NOT NULL 
              AND (patient_id IS NULL OR patient_id != id_pasien)
              AND id_pasien IN (SELECT id_pasien FROM patients)
        ');
        
        // Step 4: Buat foreign key yang benar (patient_id -> patients.id_pasien)
        Schema::table('queues', function (Blueprint $table) {
            $table->foreign('patient_id')
                  ->references('id_pasien')
                  ->on('patients')
                  ->onDelete('cascade');
        });
        
        // ✅ Fix completed: patient_id now correctly points to patients table
    }

    /**
     * Rollback: Kembalikan FK ke users (not recommended)
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Drop correct foreign key
            $table->dropForeign(['patient_id']);
        });
        
        Schema::table('queues', function (Blueprint $table) {
            // Restore wrong foreign key (for rollback only)
            $table->foreign('patient_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
