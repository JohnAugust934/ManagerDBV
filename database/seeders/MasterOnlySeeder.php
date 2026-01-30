<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MasterOnlySeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Master Admin',
            'email' => 'admin@master.com',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_master' => true,
            'club_id' => null,
        ]);

        $this->command->info('✅ Usuário Master criado: admin@master.com / password');
    }
}
