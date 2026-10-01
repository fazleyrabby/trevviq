<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Seed the demo dataset outside production. In production, seed it
        // explicitly with `php artisan db:seed --class=DemoSeeder` (the Docker
        // entrypoint does this when ROAM_SEED_DEMO=true).
        if (! app()->environment('production')) {
            $this->call(DemoSeeder::class);
        }
    }
}
