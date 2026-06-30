# SITARA SECURITY SETUP GUIDE

Panduan ini berisi langkah-langkah untuk mengaktifkan fitur keamanan yang baru saja diimplementasikan.

## 1. Environment Variables
Tambahkan variabel berikut ke file `.env` Anda untuk menyesuaikan batas Rate Limiting:

```env
# Rate Limiting Configuration
RATELIMIT_LOGIN_MAX=5
RATELIMIT_LOGIN_DECAY=1
RATELIMIT_BOOKING_MAX=10
RATELIMIT_BOOKING_DECAY=1
```

## 2. Registrasi Middleware
Daftarkan middleware baru ke dalam `bootstrap/app.php` agar dapat digunakan di dalam routes:

```php
// bootstrap/app.php

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'throttle.login' => \App\Http\Middleware\RateLimitLogin::class,
        'throttle.booking' => \App\Http\Middleware\RateLimitBooking::class,
    ]);
})
```

Setelah didaftarkan, Anda dapat menggunakannya di `routes/api.php`:
- `middleware('throttle.login')` untuk endpoint login.
- `middleware('throttle.booking')` untuk endpoint simpan antrian.

## 3. Aktivasi Enkripsi di Model
Untuk mengaktifkan fitur dekripsi otomatis di level aplikasi, tambahkan Cast ke model `Patient`:

```php
// app/Models/Patient.php

use App\Casts\EncryptedField;

protected $casts = [
    'nik' => EncryptedField::class,
    'alamat' => EncryptedField::class,
    // cast lainnya...
];
```

## 4. Konfigurasi Cron Job (Backup Otomatis)
Agar backup berjalan setiap jam 12 malam, tambahkan entri berikut ke crontab server Anda:

```bash
# Buka crontab
crontab -e

# Tambahkan baris ini (sesuaikan path project)
* * * * * cd /path/ke/project/sitaradev_hospital0 && php artisan schedule:run >> /dev/null 2>&1
```

## 5. Perintah Eksekusi
Jalankan perintah berikut untuk menerapkan perubahan database:

```bash
# Jalankan migrasi untuk tabel log backup dan enkripsi data PII
php artisan migrate

# Jalankan seeder untuk verifikasi enkripsi (Opsional)
php artisan db:seed --class=EncryptionTestSeeder
```

## 6. Lokasi Backup
File backup akan tersimpan di:
`storage/app/backups/backup_YYYY-MM-DD_HH-mm-ss.sql.gz`
Log keberhasilan backup dapat dilihat di tabel `backup_logs`.
