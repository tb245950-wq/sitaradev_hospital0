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
        // Just make booked_by nullable, keep it as varchar to support both string and user ID
        DB::statement('ALTER TABLE queues ALTER COLUMN booked_by DROP NOT NULL');
        DB::statement('ALTER TABLE queues ALTER COLUMN booked_by DROP DEFAULT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->string('booked_by', 20)->default('admin')->change();
        });
    }
};
