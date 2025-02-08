<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Events\ProfileInformationUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('user can update profile information', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    $updatedUser = ProfileInformationUpdated::commit(
        user_id: $user->id,
        name: 'New Name',
        email: 'new@example.com'
    );

    // Assert user information was updated
    expect($updatedUser->name)->toBe('New Name')
        ->and($updatedUser->email)->toBe('new@example.com')
        ->and($updatedUser->email_verified_at)->toBeNull();

    // Assert state was updated
    expect($user->fresh()->name)->toBe('New Name')
        ->and($user->fresh()->email)->toBe('new@example.com');
});

test('email verification is sent when email changes', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    ProfileInformationUpdated::commit(
        user_id: $user->id,
        name: $user->name,
        email: 'new@example.com'
    );

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('email verification is not sent when email remains the same', function () {
    Notification::fake();

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    ProfileInformationUpdated::commit(
        user_id: $user->id,
        name: 'New Name',
        email: 'test@example.com'
    );

    Notification::assertNothingSent();

    // Assert email_verified_at was not changed
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('cannot use email that is already taken', function () {
    $user1 = User::factory()->create(['email' => 'taken@example.com']);
    $user2 = User::factory()->create(['email' => 'old@example.com']);

    expect(fn () => ProfileInformationUpdated::commit(
        user_id: $user2->id,
        name: $user2->name,
        email: 'taken@example.com'
    ))->toThrow(ValidationException::class);

    // Assert user2's email was not changed
    expect($user2->fresh()->email)->toBe('old@example.com');
});

test('validates email format', function () {
    $user = User::factory()->create();

    expect(fn () => ProfileInformationUpdated::commit(
        user_id: $user->id,
        name: $user->name,
        email: 'invalid-email'
    ))->toThrow(ValidationException::class);

    // Assert email was not changed
    expect($user->fresh()->email)->toBe($user->email);
});
