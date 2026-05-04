<?php

namespace EdStevo\Standards;

use Illuminate\Support\ServiceProvider;

class StandardsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/protocol.php', 'protocol');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/protocol.php' => config_path('protocol.php'),
        ], 'standards-config');
    }
}
