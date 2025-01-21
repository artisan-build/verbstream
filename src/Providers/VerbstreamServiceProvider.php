<?php

namespace ArtisanBuild\Verbstream\Providers;

use Illuminate\Support\ServiceProvider;

class VerbstreamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/verbstream.php', 'verbstream');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/verbstream.php' => config_path('verbstream.php'),
        ], 'verbstream');
    }
}
