<?php

namespace Lain\LaravelTestGenerator;

use Illuminate\Support\ServiceProvider;

class TestGeneratorProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'test-generator'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('test-generator.php'),
        ],'test-generator');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestGenerator::class
            ]);
        }
    }
}