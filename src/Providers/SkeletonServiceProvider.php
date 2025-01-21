<?php

namespace ArtisanBuild\Skeleton\Providers;

use Illuminate\Support\ServiceProvider;

class SkeletonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/skeleton.php', 'skeleton');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/skeleton.php' => config_path('skeleton.php'),
        ], 'skeleton');
    }
}
