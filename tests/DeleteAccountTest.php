<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Features;
use ArtisanBuild\Verbstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;

test('user accounts can be deleted', function (): void {
    $this->actingAs($user = User::factory()->create());

    Livewire::test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser');

    expect($user->fresh())->toBeNull();
})->skip(fn() => ! Features::hasAccountDeletionFeatures(), 'Account deletion is not enabled.');

test('correct password must be provided before account can be deleted', function (): void {
    $this->actingAs($user = User::factory()->create());

    Livewire::test(DeleteUserForm::class)
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
})->skip(fn() => ! Features::hasAccountDeletionFeatures(), 'Account deletion is not enabled.');
