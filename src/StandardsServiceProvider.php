<?php

namespace EdStevo\Standards;

use EdStevo\Standards\Commands\StandardsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StandardsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('standards')
            ->hasConfigFile()
            ->hasCommand(StandardsCommand::class);
    }
}
