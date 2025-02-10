<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('cliente', 'web');

        $adminPassword = env('SEED_ADMIN_PASSWORD', str()->random(16));

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Fillipy Gama',
                'password' => $adminPassword,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("Admin: admin@example.com / senha: {$adminPassword}");
    }
}
