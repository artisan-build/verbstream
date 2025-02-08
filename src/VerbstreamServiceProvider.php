<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream;

use Illuminate\Support\ServiceProvider;

class VerbstreamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        //
    }
}
