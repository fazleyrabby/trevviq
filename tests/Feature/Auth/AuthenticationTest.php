<?php

use App\Models\User;

it('renders the login screen', function () {
    $this->get(route('login'))->assertOk();
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'traveller@example.com',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'traveller@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('rejects a user with an invalid password', function () {
    User::factory()->create([
        'email' => 'traveller@example.com',
        'password' => 'password',
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'traveller@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('logs an authenticated user out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

it('rate limits login after five failed attempts', function () {
    User::factory()->create([
        'email' => 'traveller@example.com',
        'password' => 'password',
    ]);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.store'), [
            'email' => 'traveller@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login.store'), [
        'email' => 'traveller@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

it('redirects an authenticated user away from the login screen', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('dashboard'));
});
