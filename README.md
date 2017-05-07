##cyom is abbreviation of create your own module

a crud generator module for laravel

###Installation

install laravel 5.2 project first

```
composer create-project --prefer-dist laravel/laravel "your_project_name" "5.2.*"
```

then add repository package from github to your project `composer.json`

```
{
    ...

    "repositories": [
        { "type" : "git", "url" : "git@github.com:abrachist/cyom.git" }
    ],

    "require": {

        ...,

        "abrachist/cyom": "1.0"
    },

    ...
}
```

update vendor packages using `composer update`

then add class provider to your project `config/app.php`

```
'providers' => [
    ...

    Abrachist\Webadmin\CyomServiceProvider::class,
    Collective\Html\HtmlServiceProvider::class,
],
```

and add alias of laravel collective html to your project `config/app.php` too

```
'aliases' => [
    ...

    'Form' => Collective\Html\FormFacade::class,
    'HTML' => Collective\Html\HtmlFacade::class,
],
```

run `composer dumpautoload` to reload registered autoload file

and the last thing install cyom package

```
php artisan cyom:install
```

if there is an error/problem about data seeder after installation, do this step to resolve the problem

```
composer dumpautoload

php artisan db:seed --class="ModuleSeeder"
```

Enjoy !!!


![01](https://cloud.githubusercontent.com/assets/15207347/25777802/654771e4-3313-11e7-900d-18c8da50ce6b.gif)
![02](https://cloud.githubusercontent.com/assets/15207347/25777807/8c657ac8-3313-11e7-8f51-ea59766a1dd1.gif)
![03](https://cloud.githubusercontent.com/assets/15207347/25777809/9b5eac70-3313-11e7-80a2-da517b8ab840.gif)
![04](https://cloud.githubusercontent.com/assets/15207347/25777810/a780fff8-3313-11e7-99d8-0dcaa9fd2403.gif)





