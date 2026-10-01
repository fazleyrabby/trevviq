<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('renders the forgot password screen', function () {
    $this->get(route('password.request'))->assertOk();
});

it('sends a password reset link to a registered user', function () {
    $user = User::factory()->create(['email' => 'traveller@example.com']);

    Notification::fake();

    $response = $this->post(route('password.email'), [
        'email' => 'traveller@example.com',
    ]);

    $response->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('renders the reset password screen with a valid token', function () {
    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk();
});

it('resets the password with a valid token', function () {
    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertRedirect(route('login'));

    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

it('rejects a password reset with an invalid token', function () {
    $user = User::factory()->create();

    $response = $this->from(route('password.request'))->post(route('password.store'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertSessionHasErrors('email');
});
