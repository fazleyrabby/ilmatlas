<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            TaxonomySeeder::class,
            LocationSeeder::class,
            InstituteSeeder::class,
        ]);

        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'editor' => 'Editor',
            'moderator' => 'Moderator',
            'data_operator' => 'Data Operator',
            'analyst' => 'Analyst',
        ];

        foreach ($roles as $role => $name) {
            User::factory()->create([
                'name' => $name,
                'email' => "{$role}@edubase.com",
            ])->assignRole($role);
        }
    }
}
