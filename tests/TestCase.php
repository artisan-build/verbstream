<?php

namespace ArtisanBuild\Verbstream\Tests;

use ArtisanBuild\Verbstream\VerbstreamServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\FortifyServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            VerbstreamServiceProvider::class,
            FortifyServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Enable two-factor authentication features
        $app['config']->set('fortify.features', [
            \Laravel\Fortify\Features::twoFactorAuthentication([
                'confirm' => true,
                'confirmPassword' => true,
            ]),
        ]);
    }
}
