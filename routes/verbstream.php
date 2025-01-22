<?php

use ArtisanBuild\Verbstream\Http\Controllers\CurrentTeamController;
use ArtisanBuild\Verbstream\Http\Controllers\Livewire\ApiTokenController;
use ArtisanBuild\Verbstream\Http\Controllers\Livewire\PrivacyPolicyController;
use ArtisanBuild\Verbstream\Http\Controllers\Livewire\TeamController;
use ArtisanBuild\Verbstream\Http\Controllers\Livewire\TermsOfServiceController;
use ArtisanBuild\Verbstream\Http\Controllers\Livewire\UserProfileController;
use ArtisanBuild\Verbstream\Http\Controllers\TeamInvitationController;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => config('verbstream.middleware', ['web'])], function (): void {
    if (Verbstream::hasTermsAndPrivacyPolicyFeature()) {
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
    }

    $authMiddleware = config('verbstream.guard')
        ? 'auth:'.config('verbstream.guard')
        : 'auth';

    $authSessionMiddleware = config('verbstream.auth_session', false)
        ? config('verbstream.auth_session')
        : null;

    Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function (): void {
        // User & Profile...
        Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile.show');

        Route::group(['middleware' => 'verified'], function (): void {
            // API...
            if (Verbstream::hasApiFeatures()) {
                Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
            }

            // Teams...
            if (Verbstream::hasTeamFeatures()) {
                Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
                Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
                Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

                Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                    ->middleware(['signed'])
                    ->name('team-invitations.accept');
            }
        });
    });
});
