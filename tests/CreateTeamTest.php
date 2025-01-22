<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Http\Livewire\CreateTeamForm;
use Livewire\Livewire;

test('teams can be created', function (): void {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    Livewire::test(CreateTeamForm::class)
        ->set(['state' => ['name' => 'Test Team']])
        ->call('createTeam');

    expect($user->fresh()->ownedTeams)->toHaveCount(2);
    expect($user->fresh()->ownedTeams()->latest('id')->first()->name)->toEqual('Test Team');
});
