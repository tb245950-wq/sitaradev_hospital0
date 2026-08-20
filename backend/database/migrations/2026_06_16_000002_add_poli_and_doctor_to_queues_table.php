<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (!Schema::hasColumn('queues', 'poli')) {
                $table->string('poli', 50)->nullable()->after('jenis_layanan');
            }
            if (!Schema::hasColumn('queues', 'doctor_id')) {
                $table->foreignId('doctor_id')->nullable()->after('poli')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('queues', 'booked_by')) {
                $table->string('booked_by', 20)->default('admin')->after('doctor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (Schema::hasColumn('queues', 'booked_by')) {
                $table->dropColumn('booked_by');
            }
            if (Schema::hasColumn('queues', 'doctor_id')) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            }
            if (Schema::hasColumn('queues', 'poli')) {
                $table->dropColumn('poli');
            }
        });
    }
};
