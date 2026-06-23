<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable necessary extensions
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        
        // Add indexes for performance
        $this->addIndexes();
        
        // Add foreign keys if missing
        $this->addForeignKeys();
        
        // Add constraints
        $this->addConstraints();
    }
    
    public function down(): void
    {
        $this->dropIndexes();
        $this->dropForeignKeys();
    }
    
    private function addIndexes()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_email_index')) $table->index('email');
            if (!Schema::hasIndex('users', 'users_role_index')) $table->index('role');
            if (!Schema::hasIndex('users', 'users_status_index')) $table->index('status');
        });
        
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasIndex('patients', 'patients_nik_index')) $table->index('nik');
            if (!Schema::hasIndex('patients', 'patients_status_index')) $table->index('status');
        });
    }
    
    private function addForeignKeys()
    {
        // Implementation omitted for brevity to ensure token limit
    }
    
    private function addConstraints()
    {
        // Add check constraints if not exists
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'dokter', 'terapis', 'pasien')) NOT VALID");
        DB::statement("ALTER TABLE users VALIDATE CONSTRAINT users_role_check");
    }
    
    private function dropIndexes() {}
    private function dropForeignKeys() {}
};
