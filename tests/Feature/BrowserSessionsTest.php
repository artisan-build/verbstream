<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Tests\Feature;

use App\Models\User;
use ArtisanBuild\Verbstream\Events\BrowserSessionsLoggedOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('user can logout other browser sessions', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    // Create multiple sessions for the user
    DB::table('sessions')->insert([
        [
            'id' => 'session-1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent 1',
            'payload' => 'test',
            'last_activity' => time(),
        ],
        [
            'id' => 'session-2',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent 2',
            'payload' => 'test',
            'last_activity' => time(),
        ],
        [
            'id' => 'session-3',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent 3',
            'payload' => 'test',
            'last_activity' => time(),
        ],
    ]);

    BrowserSessionsLoggedOut::fire(
        user_id: $user->id,
        password: 'correct-password',
        current_session_id: 'session-2'
    );

    // Assert only the current session remains
    $remainingSessions = DB::table('sessions')
        ->where('user_id', $user->id)
        ->get();

    expect($remainingSessions)->toHaveCount(1)
        ->and($remainingSessions->first()->id)->toBe('session-2');
});

test('cannot logout sessions with incorrect password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    // Create a session
    DB::table('sessions')->insert([
        'id' => 'session-1',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'payload' => 'test',
        'last_activity' => time(),
    ]);

    expect(fn () => BrowserSessionsLoggedOut::fire(
        user_id: $user->id,
        password: 'wrong-password',
        current_session_id: 'session-1'
    ))->toThrow(RuntimeException::class, 'The provided password is incorrect.');

    // Assert session still exists
    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(1);
});

test('can logout all sessions including current one', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    // Create multiple sessions
    DB::table('sessions')->insert([
        [
            'id' => 'session-1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent 1',
            'payload' => 'test',
            'last_activity' => time(),
        ],
        [
            'id' => 'session-2',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent 2',
            'payload' => 'test',
            'last_activity' => time(),
        ],
    ]);

    BrowserSessionsLoggedOut::fire(
        user_id: $user->id,
        password: 'correct-password',
        current_session_id: null
    );

    // Assert all sessions are deleted
    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
});
