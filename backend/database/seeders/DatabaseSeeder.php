<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Membuat akun default untuk semua role.
     */
    public function run(): void
    {
        $defaultUsers = [
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@sitara.test',
                'password' => Hash::make('password123'),
                'role'     => 'super_admin',
                'status'   => 'active',
            ],
            [
                'name'     => 'Admin Klinik',
                'email'    => 'admin@sitara.test',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'status'   => 'active',
            ],
            [
                'name'     => 'Muhammad Mughni',
                'email'    => 'mughni@gmail.test',
                'password' => Hash::make('password123'),
                'role'     => 'dokter',
                'status'   => 'active',
            ],
            [
                'name'     => 'Terapis Sitara',
                'email'    => 'terapis@sitara.test',
                'password' => Hash::make('password123'),
                'role'     => 'terapis',
                'status'   => 'active',
            ],
            [
                'name'     => 'Rizky',
                'email'    => 'rizky@test.com',
                'password' => Hash::make('password123'),
                'role'     => 'pasien',
                'status'   => 'active',
            ],
        ];

        foreach ($defaultUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->call(HospitalDummySeeder500::class);
    }
}
