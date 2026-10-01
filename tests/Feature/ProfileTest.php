<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('renders the dashboard for an authenticated user', function () {
    $user = User::factory()->create(['name' => 'Dashboard User']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard User');
});

it('renders the profile screen for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee($user->name);
});

it('renders the profile edit screen for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Edit profile');
});

it('updates the profile information without changing the email', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'oldusername',
        'email' => 'keep@example.com',
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'New Name',
        'username' => 'newusername',
        'bio' => 'Hello world',
        'country_code' => 'JP',
    ]);

    $response->assertRedirect(route('profile.show'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'username' => 'newusername',
        'bio' => 'Hello world',
        'country_code' => 'JP',
        'email' => 'keep@example.com',
    ]);
});

it('rejects a username that belongs to another user', function () {
    $user = User::factory()->create(['username' => 'ownname']);

    User::factory()->create(['username' => 'takenname']);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'New Name',
        'username' => 'takenname',
    ]);

    $response->assertSessionHasErrors('username');
});

it('allows a user to keep their own username', function () {
    $user = User::factory()->create(['username' => 'myusername']);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'New Name',
        'username' => 'myusername',
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHasNoErrors();
});

it('stores an uploaded avatar on the public disk', function () {
    Storage::fake('public');

    $user = User::factory()->create(['username' => 'avataruser']);
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Avatar User',
        'username' => 'avataruser',
        'avatar' => $file,
    ]);

    $response->assertRedirect(route('profile.show'));

    $avatarPath = $user->fresh()->avatar_path;

    expect($avatarPath)->not->toBeNull();
    Storage::disk('public')->assertExists($avatarPath);
});

it('lets a viewer become a traveller', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Viewer);

    $response = $this->actingAs($user)->post(route('profile.become-traveller'));

    $response->assertRedirect(route('profile.show'));

    expect($user->fresh()->role)->toBe(UserRole::Traveller);
});

it('keeps a traveller a traveller when they post become-traveller', function () {
    $user = User::factory()->traveller()->create();

    $response = $this->actingAs($user)->post(route('profile.become-traveller'));

    $response->assertRedirect(route('profile.show'));

    expect($user->fresh()->role)->toBe(UserRole::Traveller);
});

it('redirects guests to login when visiting the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('redirects guests to login when visiting the profile', function () {
    $this->get(route('profile.show'))->assertRedirect(route('login'));
});

it('redirects guests to login when updating the profile', function () {
    $this->put(route('profile.update'), [
        'name' => 'New Name',
        'username' => 'newusername',
    ])->assertRedirect(route('login'));
});
