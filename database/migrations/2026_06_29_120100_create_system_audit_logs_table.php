<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('module'); // 'user', 'backup', 'system', 'queue', etc
            $table->string('action'); // 'create', 'update', 'delete', 'export', 'backup'
            $table->string('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('old_values')->nullable(); // JSON untuk audit trail
            $table->text('new_values')->nullable(); // JSON untuk audit trail
            $table->text('affected_records')->nullable(); // JSON untuk tracking yang dirubah
            $table->string('status')->default('success'); // 'success', 'failed', 'warning'
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'action', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_audit_logs');
    }
};
