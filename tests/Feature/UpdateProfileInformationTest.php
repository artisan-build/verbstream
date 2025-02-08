<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Events\ProfileInformationUpdated;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
    Notification::fake();
});

test('it updates profile information', function (): void {
    $user = User::factory()->create();

    ProfileInformationUpdated::commit(
        user_id: $user->id,
        name: 'New Name',
        email: 'newemail@example.com'
    );

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('newemail@example.com')
        ->and($user->email_verified_at)->toBeNull();

    // Assert email verification notification was sent exactly once
    Notification::assertSentTo($user, VerifyEmail::class, 1);
});

test('it does not send verification email when email remains unchanged', function (): void {
    $user = User::factory()->create();
    $originalEmail = $user->email;

    ProfileInformationUpdated::commit(
        user_id: $user->id,
        name: 'New Name',
        email: $originalEmail
    );

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe($originalEmail);

    // Assert no email verification notification was sent
    Notification::assertNothingSent();
});
