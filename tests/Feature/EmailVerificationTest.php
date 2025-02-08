<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
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
