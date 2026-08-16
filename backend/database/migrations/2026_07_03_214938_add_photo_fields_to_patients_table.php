<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom foto KTP & foto profil ke tabel patients.
     *
     * ktp_status:
     *   none     → belum pernah upload
     *   pending  → sudah upload, menunggu verifikasi sistem
     *   verified → NIK terverifikasi ✅
     *   rejected → ditolak (alasan ada di ktp_rejected_reason)
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Foto KTP — disimpan di storage/app/private/ktp/
            $table->string('ktp_photo')->nullable()->after('nik_hash');

            // Status verifikasi KTP
            $table->enum('ktp_status', ['none', 'pending', 'verified', 'rejected'])
                  ->default('none')
                  ->after('ktp_photo');

            // Alasan penolakan jika ktp_status = rejected
            $table->string('ktp_rejected_reason')->nullable()->after('ktp_status');

            // Kapan KTP diverifikasi
            $table->timestamp('ktp_verified_at')->nullable()->after('ktp_rejected_reason');

            // Foto profil — disimpan di storage/app/public/avatars/
            $table->string('profile_photo')->nullable()->after('ktp_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'ktp_photo',
                'ktp_status',
                'ktp_rejected_reason',
                'ktp_verified_at',
                'profile_photo',
            ]);
        });
    }
};
