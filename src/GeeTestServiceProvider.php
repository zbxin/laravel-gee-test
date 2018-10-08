<?php

namespace ZhiEq\GeeTest;

use Illuminate\Support\ServiceProvider;

class GeeTestServiceProvider extends ServiceProvider
{

    /**
     * @return string
     */

    protected function configPath()
    {
        return __DIR__ . '/../config/gee_test.php';
    }

    /**
     *
     */

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'gee_test');
        $this->app->singleton('gee_test', GeeTest::class);
        $this->app->singleton(GeeTest::class, function ($app) {
            return new GeeTest($app['config']);
        });
    }

    /**
     *
     */

    public function boot()
    {
        $this->publishes([
            $this->configPath() => config_path('gee_test.php'),
        ]);
    }
}
