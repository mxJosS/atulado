<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@atulado.com.mx'],
            [
                'name' => 'Administrador A tu lado',
                'password' => Hash::make('Atulado_2026'),
                'is_admin' => true,
                'role' => 'admin',
                'avatar_color' => 'dark',
                'email_verified_at' => now(),
            ]
        );
    }
}
