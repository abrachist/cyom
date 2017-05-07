<?php

namespace Abrachist\Webadmin\Commands;

use File;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AdminModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cyom:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instal Admin and crud generator module ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->call('migrate');
            \App\User::first();
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->error("Config your database first");
            exit();
        }

        if (\App::VERSION() >= '5.2') {
            $this->info("Generating the authentication scaffolding");
            $this->call('make:auth');
        }

        $this->info("Publishing the assets");
        $this->call('vendor:publish', ['--provider' => 'Abrachist\Webadmin\CyomServiceProvider', '--force' => true]);

        $this->info("Dumping the composer autoload");
        (new Process('composer dump-autoload'))->run();

        $this->info("Migrating the database tables into your application");
        $this->call('migrate');

        $this->info("Adding the routes");

        $routeFile = app_path('Http/routes.php');
        if (\App::VERSION() >= '5.3') {
            $routeFile = base_path('routes/web.php');
        }

        // if change some routes here then need change on field url module seeder (vice versa)
        $routes =
            <<<EOD
Route::group(['middleware' => ['auth']], function () {
    Route::get('admin', 'Admin\\AdminController@index');
    Route::get('admin/authorization', 'Admin\AdminController@authorization');
    Route::get('admin/give-role-permissions', 'Admin\\AdminController@getGiveRolePermissions');
    Route::post('admin/give-role-permissions', 'Admin\\AdminController@postGiveRolePermissions');
    Route::resource('admin/roles', 'Admin\\RolesController');
    Route::resource('admin/permissions', 'Admin\\PermissionsController');
    Route::resource('admin/users', 'Admin\\UsersController');
    
    Route::get('generator/transaction', ['uses' => '\\Abrachist\\Webadmin\\Controllers\\TransactionGeneratorController@getTransaction']);
    Route::post('generator/transaction', ['uses' => '\\Abrachist\\Webadmin\\Controllers\\TransactionGeneratorController@postTransaction']);
});
EOD;

        File::append($routeFile, "\n" . $routes);

        $this->info("Overriding the AuthServiceProvider");
        $contents = File::get(__DIR__ . '/../../publish/Providers/AuthServiceProvider.php');
        File::put(app_path('Providers/AuthServiceProvider.php'), $contents);

        $this->info("Overriding the AuthController");
        $contents = File::get(__DIR__ . '/../../publish/Controllers/Auth/AuthController.php');
        File::put(app_path('Http/Controllers/Auth/AuthController.php'), $contents);

        $this->info("Success add admin module!");

        $this->info("Dumping the composer autoload");
        (new Process('composer dump-autoload'))->run();

        $this->info("Seeds application module table");
        $this->call('db:seed', ['--class' => 'ModuleSeeder']);
    }
}
