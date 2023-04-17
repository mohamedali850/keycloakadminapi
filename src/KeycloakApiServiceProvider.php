<?php

namespace KeycloakApiServices;

use Illuminate\Support\ServiceProvider;
use App\Providers\RouteServiceProvider;

class KeycloakApiServiceProvider extends ServiceProvider
{

    public function register()
    {
        // No need to call parent::register() here
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/config/config.php' => config_path('keycloakapiservices.php'),
            ], 'config');

        }
    }
}
