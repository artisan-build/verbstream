<?php

namespace ArtisanBuild\Verbstream\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use ArtisanBuild\Verbstream\Contracts\AddsTeamMembers;
use ArtisanBuild\Verbstream\Contracts\CreatesTeams;
use ArtisanBuild\Verbstream\Contracts\DeletesTeams;
use ArtisanBuild\Verbstream\Contracts\DeletesUsers;
use ArtisanBuild\Verbstream\Contracts\InvitesTeamMembers;
use ArtisanBuild\Verbstream\Contracts\RemovesTeamMembers;
use ArtisanBuild\Verbstream\Contracts\UpdatesTeamNames;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;
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

        RateLimiter::for('two-factor', fn(Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
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
