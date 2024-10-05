<?php

namespace mindtwo\Appointable\Providers;

use Illuminate\Support\ServiceProvider;
use mindtwo\Appointable\Services\Appointable;

class AppointableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/appointable.php', 'appointable');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/appointable.php' => config_path('appointable.php'),
            ], 'appointable-config');

            $this->publishes([
                __DIR__.'/../../database/migrations/create_appointments_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_appointments_table.php'),
            ], 'appointable-migrations');
        }

        $this->app->bind(Appointable::class, function () {
            return new Appointable;
        });
        $this->app->alias(Appointable::class, 'appointable');

    }
}
