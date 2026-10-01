<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('renders the registration screen', function () {
    $this->get(route('register'))->assertOk();
});

it('registers a new user and sends a verification notification', function () {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'New Traveller',
        'username' => 'newtraveller',
        'email' => 'newtraveller@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'country_code' => 'US',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'newtraveller@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Viewer)
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('rejects registration with a username that is already taken', function () {
    User::factory()->create(['username' => 'taken']);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Another Traveller',
        'username' => 'taken',
        'email' => 'another@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('username');
    $this->assertGuest();
});

it('rejects registration with an email that is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Another Traveller',
        'username' => 'anothertraveller',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('rejects a password that is too short or not confirmed', function (string $password, string $confirmation) {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'New Traveller',
        'username' => 'newtraveller',
        'email' => 'newtraveller@example.com',
        'password' => $password,
        'password_confirmation' => $confirmation,
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
})->with([
    'too short' => ['short', 'short'],
    'not confirmed' => ['password123', 'different123'],
]);

it('rate limits registration after five attempts', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post(route('register.store'), [
            'name' => "Traveller {$attempt}",
            'username' => "traveller{$attempt}",
            'email' => "traveller{$attempt}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country_code' => 'US',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    $this->post(route('register.store'), [
        'name' => 'Traveller Six',
        'username' => 'traveller6',
        'email' => 'traveller6@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'country_code' => 'US',
    ])->assertStatus(429);
});
