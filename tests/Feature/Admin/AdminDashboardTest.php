<?php

use App\Models\Admin;

it('renders the dashboard for an authenticated administrator', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard', escape: false);
});
