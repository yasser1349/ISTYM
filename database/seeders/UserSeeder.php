<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@istym.com'],
            [
                'name' => 'Admin ISTYM',
                'email' => 'admin@istym.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_alerts' => true,
                'stock_alerts' => true,
                'order_alerts' => true,
                'maintenance_alerts' => true,
            ]
        );

        // Employee - Chaimae
        User::firstOrCreate(
            ['email' => 'chaimae@istym.com'],
            [
                'name' => 'Chaimae',
                'email' => 'chaimae@istym.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'phone' => '',
                'is_active' => true,
                'email_alerts' => true,
                'stock_alerts' => true,
                'order_alerts' => true,
                'maintenance_alerts' => true,
            ]
        );

        // Employee - Ismail
        User::firstOrCreate(
            ['email' => 'ismail@istym.com'],
            [
                'name' => 'Ismail',
                'email' => 'ismail@istym.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'phone' => '',
                'is_active' => true,
                'email_alerts' => true,
                'stock_alerts' => true,
                'order_alerts' => true,
                'maintenance_alerts' => true,
            ]
        );

        // Client users (pour test)
        User::firstOrCreate(
            ['email' => 'salma@client.com'],
            [
                'name' => 'Salma Cliente',
                'email' => 'salma@client.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'phone' => '+212612345678',
                'is_active' => true,
                'email_alerts' => false,
                'stock_alerts' => false,
                'order_alerts' => true,
                'maintenance_alerts' => false,
            ]
        );
    }
}
