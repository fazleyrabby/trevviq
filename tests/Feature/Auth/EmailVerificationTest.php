<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('renders the email verification notice for an unverified user', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('verification.notice'))
        ->assertOk();
});

it('redirects a verified user from the notice to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('verification.notice'))
        ->assertRedirect(route('dashboard'));
});

it('verifies an email through a signed link', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('resends the verification notification to an unverified user', function () {
    $user = User::factory()->unverified()->create();

    Notification::fake();

    $this->actingAs($user)
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not resend the verification notification to a verified user', function () {
    $user = User::factory()->create();

    Notification::fake();

    $this->actingAs($user)
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard'));

    Notification::assertNothingSent();
});

it('redirects guests from the verification notice to login', function () {
    $this->get(route('verification.notice'))->assertRedirect(route('login'));
});
