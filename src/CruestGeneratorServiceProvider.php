<?php

namespace Raheelshan\Cruest;
use Illuminate\Support\ServiceProvider;

class CruestGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.raheelshan.cruest', function ($app) {
            return $app['Raheelshan\Cruest\Commands\ResourceMakeCommand'];
        });
        $this->commands('command.raheelshan.cruest');

    }

}