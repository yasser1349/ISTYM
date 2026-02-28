<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create employee accounts for Chaimae, Ismail and Zakaria';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create Chaimae
        $chaimae = User::firstOrCreate(
            ['email' => 'chaimae@istym.ma'],
            [
                'name' => 'Chaimae',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'is_active' => true,
                'email_alerts' => true,
                'stock_alerts' => true,
                'order_alerts' => true,
                'maintenance_alerts' => true,
            ]
        );

        // Create Ismail
        $ismail = User::firstOrCreate(
            ['email' => 'ismail@istym.ma'],
            [
                'name' => 'Ismail',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'is_active' => true,
                'email_alerts' => true,
                'stock_alerts' => true,
                'order_alerts' => true,
                'maintenance_alerts' => true,
            ]
        );

        // Create Zakaria
        $zakaria = User::firstOrCreate(
            ['email' => 'zakaria@istym.ma'],
            [
                'name' => 'Zakaria',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'is_active' => true,
                'email_alerts' => true,
                'stock_alerts' => true,
                'order_alerts' => true,
                'maintenance_alerts' => true,
            ]
        );

        $this->info('✅ Comptes créés avec succès:');
        $this->table(
            ['Nom', 'Email', 'Rôle'],
            [
                [$chaimae->name, $chaimae->email, $chaimae->role],
                [$ismail->name, $ismail->email, $ismail->role],
                [$zakaria->name, $zakaria->email, $zakaria->role],
            ]
        );

        $this->info('');
        $this->line('Identifiants de connexion:');
        $this->line('  Utilisateur 1: chaimae@istym.ma / password');
        $this->line('  Utilisateur 2: ismail@istym.ma / password');
        $this->line('  Utilisateur 3: zakaria@istym.ma / password');
    }
}
