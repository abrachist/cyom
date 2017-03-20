<?php

namespace Abrachist\Webadmin;

use File;
use Illuminate\Support\ServiceProvider;

class CyomServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->publishes([
            __DIR__ . '/../publish/Middleware/' => app_path('Http/Middleware'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/migrations/' => database_path('migrations'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/seeds/' => database_path('seeds'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/Model/' => app_path(),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/Controllers/' => app_path('Http/Controllers'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/resources/' => base_path('resources'),
        ]);

        $this->publishes([
            __DIR__ . '/theme/' => public_path(),
        ]);

        $this->publishes([
            __DIR__ . '/config/cyom.php' => config_path('cyom.php'),
        ]);

        if (\App::VERSION() <= '5.2') {
            $this->publishes([
                __DIR__ . '/views/css/app.css' => public_path('css/app.css'),
            ]);
        }

        $this->publishes([
            __DIR__ . '/template/' => base_path('resources/template/'),
        ]);
                
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            'Abrachist\Webadmin\Commands\AdminModuleCommand',
            'Abrachist\Webadmin\Commands\GenerateModule',
            'Abrachist\Webadmin\Commands\GenerateController',
            'Abrachist\Webadmin\Commands\GenerateModel',
            'Abrachist\Webadmin\Commands\GenerateMigration',
            'Abrachist\Webadmin\Commands\GenerateView',
            'Abrachist\Webadmin\Commands\GenerateLanguage',
            'Abrachist\Webadmin\Commands\TransactionGenerator\GenerateTransactionModule',
            'Abrachist\Webadmin\Commands\TransactionGenerator\GenerateTransactionController',
            'Abrachist\Webadmin\Commands\TransactionGenerator\GenerateTransactionModel',
            'Abrachist\Webadmin\Commands\TransactionGenerator\GenerateTransactionMigration',
            'Abrachist\Webadmin\Commands\TransactionGenerator\GenerateTransactionView',
            'Abrachist\Webadmin\Commands\TransactionGenerator\GenerateTransactionLanguage'
        );
    }
}
