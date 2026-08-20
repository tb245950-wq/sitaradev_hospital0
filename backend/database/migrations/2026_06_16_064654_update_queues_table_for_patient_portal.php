<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Pastikan field patient_id ada, buat nullable dulu untuk menghindari error existing data
            if (!Schema::hasColumn('queues', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->after('id_antrian')->constrained('users')->cascadeOnDelete();
            }
            
            // Tambah field poli jika belum ada
            if (!Schema::hasColumn('queues', 'poli')) {
                $table->string('poli', 50)->nullable();
            }
            
            // Tambah field doctor_id jika belum ada
            if (!Schema::hasColumn('queues', 'doctor_id')) {
                $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            }
            
            // Tambah field booked_by jika belum ada
            if (!Schema::hasColumn('queues', 'booked_by')) {
                $table->string('booked_by', 20)->default('admin');
            }

            // Tambah field queue_number jika belum ada
            if (!Schema::hasColumn('queues', 'queue_number')) {
                $table->string('queue_number', 20)->nullable();
            }

            // Tambah field type jika belum ada
            if (!Schema::hasColumn('queues', 'type')) {
                $table->string('type', 20)->nullable();
            }
            
            // Tambah field priority jika belum ada
            if (!Schema::hasColumn('queues', 'priority')) {
                $table->string('priority', 20)->default('normal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn(['poli', 'doctor_id', 'booked_by', 'queue_number', 'type', 'priority']);
        });
    }
};
