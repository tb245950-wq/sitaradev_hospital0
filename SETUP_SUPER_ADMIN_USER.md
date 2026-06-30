# Create Super Admin User - Quick Setup

Jalankan command ini di terminal:

```bash
php artisan tinker

# Buat super admin baru
User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@sitara.com',
    'role' => 'super_admin',
    'password' => Hash::make('password123'),
    'status' => 'active'
]);

# Verify user dibuat
User::where('email', 'superadmin@sitara.com')->first();

exit
```

Atau jika user Anda sudah ada, update rolenya:

```bash
php artisan tinker

# Update existing user ke super_admin
User::where('email', 'your_email@example.com')->update(['role' => 'super_admin']);

# Verify
User::where('email', 'your_email@example.com')->first();

exit
```

Kemudian:
1. Logout dari aplikasi
2. Login dengan super_admin account
3. Sekarang seharusnya lihat SuperAdminDashboard (bukan AdminDashboard)
