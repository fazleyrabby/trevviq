<?php

use App\Models\Admin;

it('shows the admin login screen to guests', function () {
    $this->get(route('admin.login'))->assertOk();
});

it('redirects guests away from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});

it('authenticates a valid administrator and redirects to the dashboard', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@roam.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('admin.login.submit'), [
        'email' => 'admin@roam.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin, 'admin');
});

it('rejects invalid administrator credentials', function () {
    Admin::factory()->create([
        'email' => 'admin@roam.test',
        'password' => 'password',
    ]);

    $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
        'email' => 'admin@roam.test',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('admin.login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest('admin');
});

it('lets an authenticated administrator log out', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->post(route('admin.logout'));

    $response->assertRedirect(route('admin.login'));
    $this->assertGuest('admin');
});
