-- ============================================================
--  Sitaradev Hospital Management System
--  Database Schema (Pure SQL)
--  Generated: 2026-07-05
-- ============================================================

-- Buat dan pilih database
CREATE DATABASE IF NOT EXISTS sitaradev_hospital
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sitaradev_hospital;

-- ============================================================
-- 1. TABEL: users
--    Menyimpan semua pengguna sistem (admin, dokter, terapis, pasien)
-- ============================================================
CREATE TABLE users (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(255)    NOT NULL,
    email           VARCHAR(255)    NOT NULL,
    email_verified_at TIMESTAMP     NULL DEFAULT NULL,
    password        VARCHAR(255)    NOT NULL,
    remember_token  VARCHAR(100)    NULL DEFAULT NULL,
    role            VARCHAR(50)     NULL DEFAULT NULL,   -- super_admin | admin | dokter | terapis | pasien
    nip             VARCHAR(100)    NULL DEFAULT NULL,   -- Nomor Induk Pegawai
    nik             VARCHAR(16)     NULL DEFAULT NULL,   -- NIK (untuk user pasien)
    phone           VARCHAR(20)     NULL DEFAULT NULL,
    status          VARCHAR(20)     NOT NULL DEFAULT 'active', -- active | inactive | suspended
    last_login_at   TIMESTAMP       NULL DEFAULT NULL,
    deleted_at      TIMESTAMP       NULL DEFAULT NULL,
    created_at      TIMESTAMP       NULL DEFAULT NULL,
    updated_at      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email),
    UNIQUE KEY users_nip_unique   (nip),
    UNIQUE KEY users_nik_unique   (nik),
    INDEX idx_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABEL: password_reset_tokens
--    Token untuk reset password
-- ============================================================
CREATE TABLE password_reset_tokens (
    email      VARCHAR(255) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL DEFAULT NULL,

    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABEL: sessions
--    Session pengguna yang aktif
-- ============================================================
CREATE TABLE sessions (
    id            VARCHAR(255) NOT NULL,
    user_id       BIGINT UNSIGNED NULL DEFAULT NULL,
    ip_address    VARCHAR(45)  NULL DEFAULT NULL,
    user_agent    TEXT         NULL,
    payload       LONGTEXT     NOT NULL,
    last_activity INT          NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_sessions_user_id      (user_id),
    INDEX idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TABEL: personal_access_tokens
--    Token autentikasi Laravel Sanctum
-- ============================================================
CREATE TABLE personal_access_tokens (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tokenable_type VARCHAR(255)    NOT NULL,
    tokenable_id   BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(255)    NOT NULL,
    token          VARCHAR(64)     NOT NULL,
    abilities      TEXT            NULL,
    last_used_at   TIMESTAMP       NULL DEFAULT NULL,
    expires_at     TIMESTAMP       NULL DEFAULT NULL,
    created_at     TIMESTAMP       NULL DEFAULT NULL,
    updated_at     TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY personal_access_tokens_token_unique (token),
    INDEX idx_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TABEL: cache
--    Cache framework Laravel
-- ============================================================
CREATE TABLE cache (
    `key`        VARCHAR(255) NOT NULL,
    value        MEDIUMTEXT   NOT NULL,
    expiration   INT          NOT NULL,

    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    `key`      VARCHAR(255) NOT NULL,
    owner      VARCHAR(255) NOT NULL,
    expiration INT          NOT NULL,

    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TABEL: jobs
--    Antrian background job Laravel
-- ============================================================
CREATE TABLE jobs (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    queue        VARCHAR(255)    NOT NULL,
    payload      LONGTEXT        NOT NULL,
    attempts     TINYINT UNSIGNED NOT NULL,
    reserved_at  INT UNSIGNED    NULL DEFAULT NULL,
    available_at INT UNSIGNED    NOT NULL,
    created_at   INT UNSIGNED    NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_jobs_queue (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id             VARCHAR(255) NOT NULL,
    name           VARCHAR(255) NOT NULL,
    total_jobs     INT          NOT NULL,
    pending_jobs   INT          NOT NULL,
    failed_jobs    INT          NOT NULL,
    failed_job_ids LONGTEXT     NOT NULL,
    options        MEDIUMTEXT   NULL,
    cancelled_at   INT          NULL DEFAULT NULL,
    created_at     INT          NOT NULL,
    finished_at    INT          NULL DEFAULT NULL,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid       VARCHAR(255)    NOT NULL,
    connection TEXT            NOT NULL,
    queue      TEXT            NOT NULL,
    payload    LONGTEXT        NOT NULL,
    exception  LONGTEXT        NOT NULL,
    failed_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. TABEL: polis
--    Data poliklinik yang tersedia di rumah sakit
-- ============================================================
CREATE TABLE polis (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kode        VARCHAR(20)     NOT NULL,
    nama        VARCHAR(100)    NOT NULL,
    deskripsi   TEXT            NULL,
    status      ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at  TIMESTAMP       NULL DEFAULT NULL,
    updated_at  TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY polis_kode_unique (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. TABEL: patients
--    Data pasien rumah sakit
-- ============================================================
CREATE TABLE patients (
    id_pasien           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nrm                 VARCHAR(50)     NOT NULL,   -- Nomor Rekam Medis
    nik                 VARCHAR(20)     NOT NULL,
    nik_hash            VARCHAR(255)    NULL DEFAULT NULL,
    nama_lengkap        VARCHAR(255)    NOT NULL,
    nama_panggilan      VARCHAR(255)    NULL DEFAULT NULL,
    tanggal_lahir       DATE            NULL DEFAULT NULL,
    jenis_kelamin       ENUM('L', 'P')  NOT NULL,
    alamat              TEXT            NOT NULL,
    no_telepon_wali     VARCHAR(255)    NOT NULL,
    nama_wali           VARCHAR(255)    NOT NULL,
    hubungan_wali       VARCHAR(255)    NOT NULL,
    riwayat_medis       TEXT            NULL,
    user_id             BIGINT UNSIGNED NULL DEFAULT NULL,  -- Link ke akun user (jika ada)
    foto_url            VARCHAR(255)    NULL DEFAULT NULL,
    foto_path           VARCHAR(255)    NULL DEFAULT NULL,
    deleted_at          TIMESTAMP       NULL DEFAULT NULL,
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id_pasien),
    UNIQUE KEY patients_nrm_unique (nrm),
    UNIQUE KEY patients_nik_unique (nik),
    CONSTRAINT fk_patients_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. TABEL: queues
--    Antrian kunjungan pasien
-- ============================================================
CREATE TABLE queues (
    id_antrian          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_pasien           BIGINT UNSIGNED NOT NULL,
    id_pengguna         BIGINT UNSIGNED NULL DEFAULT NULL,  -- Staf yang mendaftarkan
    nomor_antrian       INT             NOT NULL,
    jenis_layanan       ENUM('assessment', 'terapi') NOT NULL,
    status              ENUM('menunggu', 'dipanggil', 'selesai', 'tidak_hadir') NOT NULL DEFAULT 'menunggu',
    prioritas           INT             NOT NULL DEFAULT 0,
    poli                VARCHAR(50)     NULL DEFAULT NULL,
    id_dokter           BIGINT UNSIGNED NULL DEFAULT NULL,
    booked_by           VARCHAR(50)     NULL DEFAULT NULL,  -- 'staff' | 'pasien'
    waktu_daftar        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    waktu_panggil       TIMESTAMP       NULL DEFAULT NULL,
    waktu_selesai       TIMESTAMP       NULL DEFAULT NULL,
    catatan             TEXT            NULL,
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id_antrian),
    CONSTRAINT fk_queues_pasien
        FOREIGN KEY (id_pasien) REFERENCES patients (id_pasien) ON DELETE CASCADE,
    CONSTRAINT fk_queues_pengguna
        FOREIGN KEY (id_pengguna) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_queues_dokter
        FOREIGN KEY (id_dokter) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. TABEL: medical_assessments
--     Hasil asesmen medis pasien oleh dokter
-- ============================================================
CREATE TABLE medical_assessments (
    id_assessment       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_pasien           BIGINT UNSIGNED NOT NULL,
    id_pengguna         BIGINT UNSIGNED NOT NULL,  -- Dokter yang asesmen
    id_antrian          BIGINT UNSIGNED NULL DEFAULT NULL,
    keluhan_utama       TEXT            NOT NULL,
    diagnosis           TEXT            NOT NULL,
    catatan_medis       TEXT            NULL,
    hasil_pemeriksaan   JSON            NULL,
    rencana_terapi      TEXT            NOT NULL,
    riwayat_penyakit    TEXT            NULL,
    status              ENUM('draft', 'final') NOT NULL DEFAULT 'draft',
    tanggal_assessment  DATE            NOT NULL,
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id_assessment),
    CONSTRAINT fk_assessments_pasien
        FOREIGN KEY (id_pasien) REFERENCES patients (id_pasien) ON DELETE CASCADE,
    CONSTRAINT fk_assessments_pengguna
        FOREIGN KEY (id_pengguna) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_assessments_antrian
        FOREIGN KEY (id_antrian) REFERENCES queues (id_antrian) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. TABEL: therapies
--     Program terapi yang diberikan kepada pasien
-- ============================================================
CREATE TABLE therapies (
    id_terapi           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_assessment       BIGINT UNSIGNED NOT NULL,
    id_pasien           BIGINT UNSIGNED NOT NULL,
    id_terapis          BIGINT UNSIGNED NOT NULL,
    nama_terapi         VARCHAR(255)    NOT NULL,
    deskripsi           TEXT            NULL,
    dosis               VARCHAR(255)    NULL DEFAULT NULL,
    durasi_hari         INT             NOT NULL,
    frekuensi_per_minggu INT            NOT NULL,
    status              ENUM('terjadwal', 'berjalan', 'selesai', 'dihentikan') NOT NULL DEFAULT 'terjadwal',
    tanggal_mulai       DATE            NOT NULL,
    tanggal_selesai     DATE            NULL DEFAULT NULL,
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id_terapi),
    CONSTRAINT fk_therapies_assessment
        FOREIGN KEY (id_assessment) REFERENCES medical_assessments (id_assessment) ON DELETE CASCADE,
    CONSTRAINT fk_therapies_pasien
        FOREIGN KEY (id_pasien) REFERENCES patients (id_pasien) ON DELETE CASCADE,
    CONSTRAINT fk_therapies_terapis
        FOREIGN KEY (id_terapis) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. TABEL: therapy_monitorings
--     Monitoring sesi terapi harian pasien
-- ============================================================
CREATE TABLE therapy_monitorings (
    id_monitoring       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_terapi           BIGINT UNSIGNED NOT NULL,
    id_pasien           BIGINT UNSIGNED NOT NULL,
    id_terapis          BIGINT UNSIGNED NOT NULL,
    tanggal_sesi        DATE            NOT NULL,
    waktu_mulai         TIME            NOT NULL,
    waktu_selesai       TIME            NOT NULL,
    kehadiran           ENUM('hadir', 'tidak_hadir', 'izin') NOT NULL,
    catatan_perkembangan TEXT           NOT NULL,
    kondisi_pasien      TEXT            NOT NULL,
    rekomendasi         TEXT            NULL,
    progress_score      INT             NULL DEFAULT NULL,  -- 0 sampai 100
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id_monitoring),
    CONSTRAINT fk_monitoring_terapi
        FOREIGN KEY (id_terapi) REFERENCES therapies (id_terapi) ON DELETE CASCADE,
    CONSTRAINT fk_monitoring_pasien
        FOREIGN KEY (id_pasien) REFERENCES patients (id_pasien) ON DELETE CASCADE,
    CONSTRAINT fk_monitoring_terapis
        FOREIGN KEY (id_terapis) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. TABEL: reports
--     Laporan yang dibuat oleh staf/admin
-- ============================================================
CREATE TABLE reports (
    id_laporan          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_pengguna         BIGINT UNSIGNED NOT NULL,
    tipe_laporan        ENUM('harian', 'mingguan', 'bulanan', 'evaluasi_pasien') NOT NULL,
    judul               VARCHAR(255)    NOT NULL,
    periode_mulai       DATE            NOT NULL,
    periode_selesai     DATE            NOT NULL,
    ringkasan_isi       TEXT            NOT NULL,
    file_path           VARCHAR(255)    NULL DEFAULT NULL,
    status              ENUM('draft', 'final') NOT NULL DEFAULT 'draft',
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id_laporan),
    CONSTRAINT fk_reports_pengguna
        FOREIGN KEY (id_pengguna) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. TABEL: activity_logs
--     Log aktivitas pengguna di dalam sistem
-- ============================================================
CREATE TABLE activity_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NULL DEFAULT NULL,
    action          VARCHAR(255)    NOT NULL,
    description     TEXT            NULL,
    ip_address      VARCHAR(45)     NULL DEFAULT NULL,
    created_at      TIMESTAMP       NULL DEFAULT NULL,
    updated_at      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    CONSTRAINT fk_activity_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. TABEL: login_histories
--     Riwayat login pengguna (berhasil & gagal)
-- ============================================================
CREATE TABLE login_histories (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NULL DEFAULT NULL,
    email           VARCHAR(255)    NOT NULL,
    ip_address      VARCHAR(45)     NULL DEFAULT NULL,
    user_agent      VARCHAR(255)    NULL DEFAULT NULL,
    browser         VARCHAR(255)    NULL DEFAULT NULL,
    os              VARCHAR(255)    NULL DEFAULT NULL,
    success         TINYINT(1)      NOT NULL DEFAULT 1,
    failure_reason  VARCHAR(255)    NULL DEFAULT NULL,
    login_at        TIMESTAMP       NOT NULL,
    created_at      TIMESTAMP       NULL DEFAULT NULL,
    updated_at      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    INDEX idx_login_user_time  (user_id, login_at),
    INDEX idx_login_email_time (email, login_at),
    INDEX idx_login_success    (success),
    CONSTRAINT fk_login_histories_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. TABEL: system_audit_logs
--     Audit trail seluruh perubahan data penting di sistem
-- ============================================================
CREATE TABLE system_audit_logs (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id             BIGINT UNSIGNED NULL DEFAULT NULL,
    module              VARCHAR(255)    NOT NULL,   -- user | backup | queue | patient | dll
    action              VARCHAR(255)    NOT NULL,   -- create | update | delete | export | backup
    description         VARCHAR(255)    NOT NULL,
    ip_address          VARCHAR(45)     NULL DEFAULT NULL,
    old_values          TEXT            NULL,       -- JSON sebelum perubahan
    new_values          TEXT            NULL,       -- JSON sesudah perubahan
    affected_records    TEXT            NULL,       -- JSON record yang terdampak
    status              VARCHAR(50)     NOT NULL DEFAULT 'success',  -- success | failed | warning
    error_message       TEXT            NULL,
    created_at          TIMESTAMP       NULL DEFAULT NULL,
    updated_at          TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    INDEX idx_audit_user_time   (user_id, created_at),
    INDEX idx_audit_module      (module, action, created_at),
    INDEX idx_audit_status      (status),
    CONSTRAINT fk_audit_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. TABEL: backup_logs
--     Log proses backup database
-- ============================================================
CREATE TABLE backup_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    filename        VARCHAR(255)    NOT NULL,
    status          VARCHAR(50)     NOT NULL,  -- success | failed
    size_bytes      BIGINT          NULL DEFAULT NULL,
    error_message   TEXT            NULL,
    started_at      TIMESTAMP       NULL DEFAULT NULL,
    completed_at    TIMESTAMP       NULL DEFAULT NULL,
    created_at      TIMESTAMP       NULL DEFAULT NULL,
    updated_at      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA AWAL (SEED)
-- ============================================================

-- Poli default
INSERT INTO polis (kode, nama, deskripsi, status, created_at, updated_at) VALUES
('umum',           'Poli Umum',           'Konsultasi umum dan pemeriksaan awal',        'aktif', NOW(), NOW()),
('psikolog',       'Poli Psikolog',        'Konsultasi psikologi anak dan keluarga',      'aktif', NOW(), NOW()),
('terapi',         'Poli Terapi',          'Terapi wicara, okupasi, dan fisioterapi',     'aktif', NOW(), NOW()),
('tumbuh_kembang', 'Poli Tumbuh Kembang',  'Pemantauan tumbuh kembang anak',             'aktif', NOW(), NOW());

-- Akun Super Admin default
-- Password: Admin@1234 (bcrypt hash)
INSERT INTO users (name, email, password, role, status, created_at, updated_at) VALUES
('Super Admin',  'superadmin@sitaradev.com',  '$2y$12$placeholder_hash_superadmin',  'super_admin', 'active', NOW(), NOW()),
('Admin Klinik', 'admin@sitaradev.com',        '$2y$12$placeholder_hash_admin',        'admin',       'active', NOW(), NOW()),
('Dr. Budi',     'dokter@sitaradev.com',       '$2y$12$placeholder_hash_dokter',       'dokter',      'active', NOW(), NOW()),
('Terapis Sari', 'terapis@sitaradev.com',      '$2y$12$placeholder_hash_terapis',      'terapis',     'active', NOW(), NOW());

-- ============================================================
-- ALTER TABLE — Perubahan Struktur Tabel
-- ============================================================

-- ── users ────────────────────────────────────────────────────
-- Tambah kolom foto profil
ALTER TABLE users
    ADD COLUMN foto_profil VARCHAR(255) NULL DEFAULT NULL AFTER phone;

-- Ubah panjang kolom name menjadi lebih besar
ALTER TABLE users
    MODIFY COLUMN name VARCHAR(300) NOT NULL;

-- Tambah kolom catatan internal untuk user
ALTER TABLE users
    ADD COLUMN catatan TEXT NULL AFTER status;

-- Buat index baru untuk pencarian berdasarkan nama
ALTER TABLE users
    ADD INDEX idx_users_name (name);

-- Hapus index yang tidak terpakai (contoh)
ALTER TABLE users
    DROP INDEX idx_users_name;

-- ── patients ─────────────────────────────────────────────────
-- Tambah kolom golongan darah
ALTER TABLE patients
    ADD COLUMN golongan_darah ENUM('A', 'B', 'AB', 'O') NULL DEFAULT NULL AFTER jenis_kelamin;

-- Tambah kolom alergi obat
ALTER TABLE patients
    ADD COLUMN alergi_obat TEXT NULL DEFAULT NULL AFTER riwayat_medis;

-- Ubah tipe kolom no_telepon_wali agar bisa menyimpan nomor panjang
ALTER TABLE patients
    MODIFY COLUMN no_telepon_wali VARCHAR(30) NOT NULL;

-- Tambah index untuk pencarian berdasarkan nama lengkap
ALTER TABLE patients
    ADD INDEX idx_patients_nama (nama_lengkap);

-- ── queues ───────────────────────────────────────────────────
-- Tambah kolom tanggal kunjungan yang dipisah dari waktu_daftar
ALTER TABLE queues
    ADD COLUMN tanggal_kunjungan DATE NULL DEFAULT NULL AFTER poli;

-- Tambah index untuk mempercepat filter status antrian
ALTER TABLE queues
    ADD INDEX idx_queues_status_tanggal (status, tanggal_kunjungan);

-- ── medical_assessments ──────────────────────────────────────
-- Tambah kolom tinggi badan dan berat badan
ALTER TABLE medical_assessments
    ADD COLUMN tinggi_badan DECIMAL(5,2) NULL DEFAULT NULL AFTER tanggal_assessment,
    ADD COLUMN berat_badan  DECIMAL(5,2) NULL DEFAULT NULL AFTER tinggi_badan;

-- ── therapies ────────────────────────────────────────────────
-- Tambah kolom biaya terapi
ALTER TABLE therapies
    ADD COLUMN biaya DECIMAL(12,2) NULL DEFAULT 0.00 AFTER dosis;

-- ── therapy_monitorings ──────────────────────────────────────
-- Tambah kolom foto dokumentasi sesi
ALTER TABLE therapy_monitorings
    ADD COLUMN foto_dokumentasi VARCHAR(255) NULL DEFAULT NULL AFTER rekomendasi;

-- ── backup_logs ──────────────────────────────────────────────
-- Tambah kolom tipe backup (manual / auto)
ALTER TABLE backup_logs
    ADD COLUMN tipe VARCHAR(20) NOT NULL DEFAULT 'manual' AFTER filename;

-- ── polis ────────────────────────────────────────────────────
-- Rename kolom nama menjadi nama_poli (contoh RENAME COLUMN, MySQL 8.0+)
ALTER TABLE polis
    RENAME COLUMN nama TO nama_poli;

-- Kembalikan nama kolom semula
ALTER TABLE polis
    RENAME COLUMN nama_poli TO nama;

-- ── Contoh RENAME TABLE ──────────────────────────────────────
-- Ubah nama tabel sementara (hati-hati di production!)
-- ALTER TABLE activity_logs RENAME TO user_activity_logs;
-- ALTER TABLE user_activity_logs RENAME TO activity_logs;


-- ============================================================
-- DROP — Menghapus Kolom, Index, Constraint, & Tabel
-- ============================================================

-- ── Hapus kolom yang sudah tidak dipakai ─────────────────────
-- Hapus kolom catatan yang tadi ditambahkan di users
ALTER TABLE users
    DROP COLUMN catatan;

-- Hapus kolom foto_profil dari users
ALTER TABLE users
    DROP COLUMN foto_profil;

-- ── Hapus index ──────────────────────────────────────────────
-- Hapus index nama pasien
ALTER TABLE patients
    DROP INDEX idx_patients_nama;

-- Hapus index status antrian
ALTER TABLE queues
    DROP INDEX idx_queues_status_tanggal;

-- ── Hapus Foreign Key ────────────────────────────────────────
-- Contoh melepas FK sebelum restrukturisasi (aktifkan jika diperlukan)
-- ALTER TABLE queues DROP FOREIGN KEY fk_queues_dokter;
-- ALTER TABLE queues ADD CONSTRAINT fk_queues_dokter
--     FOREIGN KEY (id_dokter) REFERENCES users (id) ON DELETE SET NULL;

-- ── DROP TABLE (tabel sementara / tidak terpakai) ────────────
-- Hapus tabel hanya jika benar-benar tidak dibutuhkan lagi.
-- Diberi komentar sebagai pengaman — aktifkan secara manual jika perlu.

-- DROP TABLE IF EXISTS failed_jobs;
-- DROP TABLE IF EXISTS job_batches;
-- DROP TABLE IF EXISTS cache_locks;
-- DROP TABLE IF EXISTS cache;


-- ============================================================
-- PRIVILEGE — Manajemen Hak Akses Database User
-- ============================================================

-- ── Buat user database khusus per role ───────────────────────

-- User untuk aplikasi (baca + tulis, tanpa DROP/ALTER)
CREATE USER IF NOT EXISTS 'sitara_app'@'localhost'     IDENTIFIED BY 'AppPass@2026!';
CREATE USER IF NOT EXISTS 'sitara_app'@'%'             IDENTIFIED BY 'AppPass@2026!';

-- User read-only untuk laporan / BI tool
CREATE USER IF NOT EXISTS 'sitara_readonly'@'localhost' IDENTIFIED BY 'ReadOnly@2026!';
CREATE USER IF NOT EXISTS 'sitara_readonly'@'%'         IDENTIFIED BY 'ReadOnly@2026!';

-- User khusus backup (hanya SELECT + LOCK TABLES)
CREATE USER IF NOT EXISTS 'sitara_backup'@'localhost'   IDENTIFIED BY 'Backup@2026!';

-- User DBA (akses penuh, hanya dari localhost)
CREATE USER IF NOT EXISTS 'sitara_dba'@'localhost'      IDENTIFIED BY 'DbaPass@2026!';


-- ── GRANT — Berikan hak akses ────────────────────────────────

-- sitara_app: bisa SELECT, INSERT, UPDATE, DELETE, EXECUTE
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE
    ON sitaradev_hospital.*
    TO 'sitara_app'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE
    ON sitaradev_hospital.*
    TO 'sitara_app'@'%';

-- sitara_readonly: hanya boleh SELECT
GRANT SELECT
    ON sitaradev_hospital.*
    TO 'sitara_readonly'@'localhost';

GRANT SELECT
    ON sitaradev_hospital.*
    TO 'sitara_readonly'@'%';

-- sitara_backup: SELECT + LOCK TABLES + SHOW VIEW + RELOAD
GRANT SELECT, LOCK TABLES, SHOW VIEW, RELOAD, REPLICATION CLIENT
    ON *.*
    TO 'sitara_backup'@'localhost';

-- sitara_dba: akses penuh hanya dari localhost
GRANT ALL PRIVILEGES
    ON sitaradev_hospital.*
    TO 'sitara_dba'@'localhost';


-- ── REVOKE — Cabut hak akses tertentu ────────────────────────

-- Cabut hak DELETE dari sitara_app di host '%' (eksternal)
-- (biarkan akses internal localhost tetap lengkap)
REVOKE DELETE
    ON sitaradev_hospital.*
    FROM 'sitara_app'@'%';

-- Cabut hak INSERT dari readonly jika salah diberikan
-- REVOKE INSERT ON sitaradev_hospital.* FROM 'sitara_readonly'@'localhost';


-- ── Terapkan perubahan privilege ─────────────────────────────
FLUSH PRIVILEGES;


-- ── Cek privilege yang sudah diberikan ───────────────────────
-- Jalankan query ini secara manual untuk verifikasi:
-- SHOW GRANTS FOR 'sitara_app'@'localhost';
-- SHOW GRANTS FOR 'sitara_readonly'@'localhost';
-- SHOW GRANTS FOR 'sitara_backup'@'localhost';
-- SHOW GRANTS FOR 'sitara_dba'@'localhost';


-- ============================================================
-- SELESAI
-- Jalankan: mysql -u root -p < sitaradev_hospital.sql
-- ============================================================
