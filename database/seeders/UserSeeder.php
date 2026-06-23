<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Administrator', 'email'=>'superadmin@itm.co.id', 'role'=>'superadmin', 'password'=>'adminITM$2026$$'],
            ['name' => 'Operator Harian', 'email'=> 'operator@itm.co.id', 'role'=>'operator', 'password'=>'operatorITM$2026$$']
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'              => $u['name'],
                    'role'              => $u['role'],
                    'password'          => Hash::make($u['password'] ?? 'password'),
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]
            );
        }
    }
}