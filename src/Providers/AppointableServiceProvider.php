<?php

namespace mindtwo\Appointable\Providers;

use Illuminate\Support\ServiceProvider;
use mindtwo\Appointable\Services\Appointable;

class AppointableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->publishMigration();

        $this->registerConfig();
    }

    public function boot(): void
    {
        $this->app->bind(Appointable::class, function () {
            return new Appointable;
        });
        $this->app->alias(Appointable::class, 'appointable');

    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/appointable.php', 'appointable');

        $this->publishes([
            __DIR__.'/../../config/appointable.php' => config_path('appointable.php'),
        ], 'appointable-config');
    }

    /**
     * Publish migration.
     *
     * @return void
     */
    protected function publishMigration()
    {
        $this->publishes([
            __DIR__.'/../../database/migrations/create_appointments_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_appointments_table.php'),
        ], 'appointable-migrations');
    }
}
