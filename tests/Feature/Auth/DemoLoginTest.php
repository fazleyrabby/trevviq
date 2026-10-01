<?php

use App\Models\Admin;
use App\Models\User;

it('logs in the demo traveller when demo mode is enabled', function () {
    config(['roam.demo.enabled' => true, 'roam.demo.user_email' => 'traveller@roam.test']);

    $user = User::factory()->create(['email' => 'traveller@roam.test']);

    $this->post(route('demo.login'))->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('logs in the demo administrator when demo mode is enabled', function () {
    config(['roam.demo.enabled' => true, 'roam.demo.admin_email' => 'admin@roam.test']);

    $admin = Admin::factory()->create(['email' => 'admin@roam.test']);

    $this->post(route('admin.demo-login'))->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin, 'admin');
});

it('is unavailable when demo mode is disabled', function () {
    config(['roam.demo.enabled' => false]);

    $this->post(route('demo.login'))->assertNotFound();
    $this->post(route('admin.demo-login'))->assertNotFound();
});

it('reports an error when the demo account has not been seeded', function () {
    config(['roam.demo.enabled' => true, 'roam.demo.user_email' => 'missing@roam.test']);

    $this->from(route('login'))
        ->post(route('demo.login'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
