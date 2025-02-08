<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Actions\CreateNewUser;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
    Notification::fake();
});

test('it creates a user with personal team', function (): void {
    $creator = new CreateNewUser;

    $user = $creator->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Verbstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    // Assert user was created
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com');

    // Assert personal team was created
    $personalTeam = Team::where('user_id', $user->id)
        ->where('personal_team', true)
        ->first();

    expect($personalTeam)->toBeInstanceOf(Team::class)
        ->and($personalTeam->name)->toBe("Test's Team")
        ->and($user->current_team_id)->toBe($personalTeam->id)
        ->and(User::find($user->id)->current_team_id)->toBe($personalTeam->id)
        ->and($user->teams)->toHaveCount(1)
        ->and($user->teams->first()->id)->toBe($personalTeam->id);

    // Assert email verification notification was sent exactly once
    Notification::assertSentTo($user, VerifyEmail::class);
    Notification::assertCount(1);
});
