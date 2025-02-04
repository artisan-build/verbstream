<?php

namespace ArtisanBuild\Verbstream\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'verbstream:install';

    protected $description = 'Install this package';

    public function handle(): int
    {
        // Publish the configuration files

        // Publish the stubs

        return self::SUCCESS;
    }
}
