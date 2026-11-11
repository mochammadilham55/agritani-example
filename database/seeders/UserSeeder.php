<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Admin AgriTani', 'admin@agritani.test', 'admin', '081200000001'],
            ['Pak Slamet (Petani)', 'petani@agritani.test', 'petani', '081200000002'],
            ['Bu Sari (Petani)', 'petani2@agritani.test', 'petani', '081200000003'],
            ['Budi Pembeli', 'pembeli@agritani.test', 'pembeli', '081200000004'],
        ];

        foreach ($users as [$name, $email, $role, $phone]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password', // di-hash otomatis oleh cast
                'role' => $role,
                'phone' => $phone,
                'address' => 'Jl. Contoh No. 1, Indonesia',
                'is_active' => true,
            ]);
        }
    }
}
