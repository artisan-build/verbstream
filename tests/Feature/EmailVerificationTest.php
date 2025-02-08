<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Events\EmailVerificationNotificationSent;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
    Notification::fake();
});

test('unverified user can request verification email resend', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertStatus(302);

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('verified user cannot request verification email resend', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertStatus(302);

    Notification::assertNothingSent();
});

test('guest cannot request verification email resend', function () {
    $response = $this->post(route('verification.send'));

    $response->assertRedirect(route('login'));
});

test('it sends verification email when manually requested', function () {
    $user = User::factory()->unverified()->create();

    EmailVerificationNotificationSent::fire(user_id: $user->id);

    // Assert email verification notification was sent exactly once
    Notification::assertSentTo($user, VerifyEmail::class, 1);
});

test('it does not send verification email to already verified users', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    EmailVerificationNotificationSent::fire(user_id: $user->id);

    // Assert no email verification notification was sent
    Notification::assertNothingSent();
});
