<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\UserCreated;
use Illuminate\Support\Facades\Hash;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('it creates a user with personal team', function () {
    $user = UserCreated::commit(
        name: 'Test User',
        email: 'test@example.com',
        password: Hash::make('password')
    );

    // Assert user was created
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com');

    // Assert personal team was created
    $personalTeam = Team::where('user_id', $user->id)
        ->where('personal_team', true)
        ->first();

    expect($personalTeam)->toBeInstanceOf(Team::class)
        ->and($personalTeam->name)->toBe("Test's Team");

    // Assert current_team_id was set
    expect($user->current_team_id)->toBe($personalTeam->id);
    expect(User::find($user->id)->current_team_id)->toBe($personalTeam->id);

    // Assert pivot table record exists
    expect($user->teams)->toHaveCount(1)
        ->and($user->teams->first()->id)->toBe($personalTeam->id);
});
