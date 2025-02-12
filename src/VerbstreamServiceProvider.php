<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream;

use ArtisanBuild\Verbstream\Actions\CreateNewUser;
use ArtisanBuild\Verbstream\Actions\ResetUserPassword;
use ArtisanBuild\Verbstream\Actions\UpdateUserPassword;
use ArtisanBuild\Verbstream\Actions\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Fortify;
use Override;
use Symfony\Component\HttpFoundation\Response;

class VerbstreamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'verbstream');

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton(LoginViewResponse::class, fn () => new class implements LoginViewResponse
        {
            public function toResponse($request): Response
            {
                return response()->view('verbstream::auth.login');
            }
        });

        $this->app->singleton(RegisterViewResponse::class, fn () => new class implements RegisterViewResponse
        {
            public function toResponse($request): Response
            {
                return response()->view('verbstream::auth.register');
            }
        });

        $this->app->singleton(RequestPasswordResetLinkViewResponse::class, fn () => new class implements RequestPasswordResetLinkViewResponse
        {
            public function toResponse($request): Response
            {
                return response()->view('verbstream::auth.forgot-password');
            }
        });

        $this->app->singleton(ResetPasswordViewResponse::class, fn () => new class implements ResetPasswordViewResponse
        {
            public function toResponse($request): Response
            {
                return response()->view('verbstream::auth.reset-password', ['request' => $request]);
            }
        });

        $this->app->singleton(VerifyEmailViewResponse::class, fn () => new class implements VerifyEmailViewResponse
        {
            public function toResponse($request): Response
            {
                return response()->view('verbstream::auth.verify-email');
            }
        });
    }
}
