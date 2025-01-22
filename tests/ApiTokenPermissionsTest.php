<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Features;
use ArtisanBuild\Verbstream\Http\Livewire\ApiTokenManager;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('api token permissions can be updated', function (): void {
    if (Features::hasTeamFeatures()) {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());
    } else {
        $this->actingAs($user = User::factory()->create());
    }

    $token = $user->tokens()->create([
        'name' => 'Test Token',
        'token' => Str::random(40),
        'abilities' => ['create', 'read'],
    ]);

    Livewire::test(ApiTokenManager::class)
        ->set(['managingPermissionsFor' => $token])
        ->set(['updateApiTokenForm' => [
            'permissions' => [
                'delete',
                'missing-permission',
            ],
        ]])
        ->call('updateApiToken');

    expect($user->fresh()->tokens->first())
        ->can('delete')->toBeTrue()
        ->can('read')->toBeFalse()
        ->can('missing-permission')->toBeFalse();
})->skip(fn() => ! Features::hasApiFeatures(), 'API support is not enabled.');
