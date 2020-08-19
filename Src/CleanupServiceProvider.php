<?php

namespace Rohits\Src;

use Illuminate\Support\ServiceProvider;
use Rohits\Src\Commands\CleanUpCommand;

class CleanUpserviceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('cleanup.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanUpCommand::class,
            ]);
        }
    }
}
