<?php

namespace VendorName\Skeleton;

use Illuminate\Support\ServiceProvider;
use VendorName\Skeleton\Commands\InstallCommand;

class SkeletonServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([InstallCommand::class]);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'skeleton');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
