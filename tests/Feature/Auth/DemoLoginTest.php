<?php

use App\Models\Admin;
use App\Models\User;

it('logs in the demo traveller when demo mode is enabled', function () {
    config(['trevviq.demo.enabled' => true, 'trevviq.demo.user_email' => 'traveller@trevviq.test']);

    $user = User::factory()->create(['email' => 'traveller@trevviq.test']);

    $this->post(route('demo.login'))->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('logs in the demo administrator when demo mode is enabled', function () {
    config(['trevviq.demo.enabled' => true, 'trevviq.demo.admin_email' => 'admin@trevviq.test']);

    $admin = Admin::factory()->create(['email' => 'admin@trevviq.test']);

    $this->post(route('admin.demo-login'))->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin, 'admin');
});

it('is unavailable when demo mode is disabled', function () {
    config(['trevviq.demo.enabled' => false]);

    $this->post(route('demo.login'))->assertNotFound();
    $this->post(route('admin.demo-login'))->assertNotFound();
});

it('reports an error when the demo account has not been seeded', function () {
    config(['trevviq.demo.enabled' => true, 'trevviq.demo.user_email' => 'missing@trevviq.test']);

    $this->from(route('login'))
        ->post(route('demo.login'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
