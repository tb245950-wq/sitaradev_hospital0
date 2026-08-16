<?php

/**
 * Konfigurasi terpusat untuk upload file.
 *
 * Semua aturan ukuran, tipe file, dan path disimpan di sini.
 * Jika nanti pindah ke cloud (S3/R2), cukup ganti 'disk' saja.
 */

return [

    // ── Foto KTP ─────────────────────────────────────────────────────────
    'ktp' => [
        // Disk storage — 'local' = storage/app/  (private, tidak bisa diakses publik)
        'disk'      => 'local',

        // Path relatif di dalam disk
        'path'      => 'private/ktp',

        // Ukuran maksimal dalam kilobytes (5120 KB = 5 MB)
        'max_size'  => 5120,

        // MIME type yang diizinkan
        'mimes'     => ['image/jpeg', 'image/png', 'image/webp'],

        // Ekstensi yang diizinkan (untuk validasi Laravel)
        'extensions' => 'jpeg,jpg,png,webp',

        // Dimensi minimum foto KTP (lebar x tinggi dalam pixel)
        'min_width'  => 400,
        'min_height' => 250,
    ],

    // ── Foto Profil ───────────────────────────────────────────────────────
    'avatar' => [
        // Disk storage — 'public' = storage/app/public/ (bisa diakses via URL)
        'disk'      => 'public',

        // Path relatif di dalam disk
        'path'      => 'avatars',

        // Ukuran maksimal dalam kilobytes (2048 KB = 2 MB)
        'max_size'  => 2048,

        // MIME type yang diizinkan
        'mimes'     => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],

        // Ekstensi yang diizinkan
        'extensions' => 'jpeg,jpg,png,webp,gif',
    ],

    // ── Internal KTP Checker ──────────────────────────────────────────────
    'ktp_checker' => [
        // Apakah checker aktif (bisa dimatikan sementara)
        'enabled' => true,

        // Confidence score minimum agar dianggap valid (0.0 - 1.0)
        // Saat ini placeholder — nanti bisa diisi logika OCR/AI
        'min_confidence' => 0.8,

        // Auto-approve jika semua check lolos tanpa intervensi manual
        'auto_approve' => true,

        // Auto-reject jika ada check yang gagal (false = tetap pending untuk review)
        'auto_reject' => false,
    ],

];
