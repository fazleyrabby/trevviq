<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the bootstrap administrator account.
     *
     * Credentials are read from the environment so production deployments can
     * set them without editing code. Defaults are local-only.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@trevviq.test')],
            [
                'name' => env('ADMIN_NAME', 'Trevviq Admin'),
                'role' => 'super_admin',
                'password' => env('ADMIN_PASSWORD', 'password'),
            ],
        );
    }
}
