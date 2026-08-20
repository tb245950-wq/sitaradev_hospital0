<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
        
        // Add new constraint with super_admin
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin', 'admin', 'dokter', 'terapis', 'pasien'))");
    }

    public function down(): void
    {
        // Rollback
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'dokter', 'terapis', 'pasien'))");
    }
};
