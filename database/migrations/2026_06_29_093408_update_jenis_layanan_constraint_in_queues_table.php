<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old constraint
        DB::statement('ALTER TABLE queues DROP CONSTRAINT IF EXISTS queues_jenis_layanan_check');
        
        // Add new constraint with additional values
        DB::statement("ALTER TABLE queues ADD CONSTRAINT queues_jenis_layanan_check CHECK (jenis_layanan IN ('assessment', 'terapi', 'konsultasi', 'kontrol'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE queues DROP CONSTRAINT IF EXISTS queues_jenis_layanan_check');
        DB::statement("ALTER TABLE queues ADD CONSTRAINT queues_jenis_layanan_check CHECK (jenis_layanan IN ('assessment', 'terapi'))");
    }
};
