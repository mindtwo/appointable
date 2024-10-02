<?php

namespace mindtwo\Appointable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use mindtwo\Appointable\Providers\AppointableServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Workbench\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            AppointableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $migration = include __DIR__.'/../database/migrations/create_appointments_table.php.stub';
        $migration->up();

    }
}
