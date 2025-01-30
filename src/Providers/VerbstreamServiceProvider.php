<?php

namespace ArtisanBuild\Verbstream\Providers;

use ArtisanBuild\Verbstream\Actions\AddTeamMember;
use ArtisanBuild\Verbstream\Actions\CreateNewUser;
use ArtisanBuild\Verbstream\Actions\CreateTeam;
use ArtisanBuild\Verbstream\Actions\DeleteTeam;
use ArtisanBuild\Verbstream\Actions\DeleteUser;
use ArtisanBuild\Verbstream\Actions\InviteTeamMember;
use ArtisanBuild\Verbstream\Actions\RemoveTeamMember;
use ArtisanBuild\Verbstream\Actions\ResetUserPassword;
use ArtisanBuild\Verbstream\Actions\UpdateTeamName;
use ArtisanBuild\Verbstream\Actions\UpdateUserPassword;
use ArtisanBuild\Verbstream\Actions\UpdateUserProfileInformation;
use ArtisanBuild\Verbstream\Contracts\AddsTeamMembers;
use ArtisanBuild\Verbstream\Contracts\CreatesTeams;
use ArtisanBuild\Verbstream\Contracts\DeletesTeams;
use ArtisanBuild\Verbstream\Contracts\DeletesUsers;
use ArtisanBuild\Verbstream\Contracts\InvitesTeamMembers;
use ArtisanBuild\Verbstream\Contracts\RemovesTeamMembers;
use ArtisanBuild\Verbstream\Contracts\UpdatesTeamNames;
use ArtisanBuild\Verbstream\Features;
use ArtisanBuild\Verbstream\Http\Livewire\ApiTokenManager;
use ArtisanBuild\Verbstream\Http\Livewire\CreateTeamForm;
use ArtisanBuild\Verbstream\Http\Livewire\DeleteTeamForm;
use ArtisanBuild\Verbstream\Http\Livewire\DeleteUserForm;
use ArtisanBuild\Verbstream\Http\Livewire\LogoutOtherBrowserSessionsForm;
use ArtisanBuild\Verbstream\Http\Livewire\TeamMemberManager;
use ArtisanBuild\Verbstream\Http\Livewire\TwoFactorAuthenticationForm;
use ArtisanBuild\Verbstream\Http\Livewire\UpdatePasswordForm;
use ArtisanBuild\Verbstream\Http\Livewire\UpdateProfileInformationForm;
use ArtisanBuild\Verbstream\Http\Livewire\UpdateTeamNameForm;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Override;

class VerbstreamServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/verbstream.php', 'verbstream');
        $this->loadRoutesFrom(__DIR__.'/../../routes/verbstream.php');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/verbstream.php' => config_path('verbstream.php'),
        ], 'verbstream');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'verbstream');

        Fortify::viewPrefix('verbstream::auth.');

        RedirectResponse::macro('banner', fn ($message) =>
            /** @var RedirectResponse $this */
            $this->with('flash', [
                'bannerStyle' => 'success',
                'banner' => $message,
            ]));

        RedirectResponse::macro('warningBanner', fn ($message) =>
            /** @var RedirectResponse $this */
            $this->with('flash', [
                'bannerStyle' => 'warning',
                'banner' => $message,
            ]));

        RedirectResponse::macro('dangerBanner', fn ($message) =>
            /** @var RedirectResponse $this */
            $this->with('flash', [
                'bannerStyle' => 'danger',
                'banner' => $message,
            ]));

        Livewire::component('profile.update-profile-information-form', UpdateProfileInformationForm::class);
        Livewire::component('profile.update-password-form', UpdatePasswordForm::class);
        Livewire::component('profile.two-factor-authentication-form', TwoFactorAuthenticationForm::class);
        Livewire::component('profile.logout-other-browser-sessions-form', LogoutOtherBrowserSessionsForm::class);
        Livewire::component('profile.delete-user-form', DeleteUserForm::class);

        if (Features::hasApiFeatures()) {
            Livewire::component('api.api-token-manager', ApiTokenManager::class);
        }

        if (Features::hasTeamFeatures()) {
            Livewire::component('teams.create-team-form', CreateTeamForm::class);
            Livewire::component('teams.update-team-name-form', UpdateTeamNameForm::class);
            Livewire::component('teams.team-member-manager', TeamMemberManager::class);
            Livewire::component('teams.delete-team-form', DeleteTeamForm::class);
        }

        $this->configurePermissions();

        app()->singleton(CreatesTeams::class, CreateTeam::class);
        app()->singleton(UpdatesTeamNames::class, UpdateTeamName::class);
        app()->singleton(AddsTeamMembers::class, AddTeamMember::class);
        app()->singleton(InvitesTeamMembers::class, InviteTeamMember::class);
        app()->singleton(RemovesTeamMembers::class, RemoveTeamMember::class);
        app()->singleton(DeletesTeams::class, DeleteTeam::class);
        app()->singleton(DeletesUsers::class, DeleteUser::class);

        app()->singleton(CreatesNewUsers::class, CreateNewUser::class);
        app()->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        app()->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        app()->singleton(ResetsUserPasswords::class, ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }

    protected function configurePermissions(): void
    {
        Verbstream::defaultApiTokenPermissions(['read']);

        Verbstream::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

        Verbstream::role('editor', 'Editor', [
            'read',
            'create',
            'update',
        ])->description('Editor users have the ability to read, create, and update.');
    }
}
