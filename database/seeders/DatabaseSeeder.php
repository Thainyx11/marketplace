<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run without WithoutModelEvents: User's `saved` event keeps the
     * denormalized `role` column in sync with Spatie roles (see User::booted()).
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
