<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

// $app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    App\Domain\Interfaces\TokenRepositoryInterface::class,
    function () {
        return new App\Infrastructure\Repositories\TokenRepository(
            App\Application\Services\DatabaseConnection::connect()
        );
    }
);

$app->singleton(
    App\Domain\Interfaces\TwitchTokenRepositoryInterface::class,
    function ($app) {
        return new App\Infrastructure\Repositories\TwitchTokenRepository(
            App\Application\Services\DatabaseConnection::connect()
        );
    }
);

$app->singleton(
    App\Application\Services\DatabaseConnection::class,
    function () {
        return new App\Application\Services\DatabaseConnection();
    }
);

$app->singleton(
    App\Domain\Interfaces\UserRepositoryInterface::class,
    function ($app) {
        return new App\Infrastructure\Repositories\UserRepository(
            $app->make(App\Application\Services\DatabaseConnection::class)
        );
    }
);





/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});



/*
 * esto es temporal, es para ver que funciona de momento sin tener que organizar bien todos los archivos
 */
require_once __DIR__ . '/../app/Application/Services/DatabaseConnection.php';
require_once __DIR__ . '/../app/Application/Services/TopGamesUpdater.php';
require_once __DIR__ . '/../app/Application/Services/TwitchEnrichedStreamService.php';
require_once __DIR__ . '/../app/Application/Services/TwitchStreamService.php';
require_once __DIR__ . '/../app/Application/Services/TwitchTokenService.php';
require_once __DIR__ . '/../app/Domain/Interfaces/TwitchTokenRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TokenService.php';
require_once __DIR__ . '/../app/Domain/Interfaces/TokenRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TopsofthetopsService.php';
require_once __DIR__ . '/../app/Application/Services/UserService.php';
require_once __DIR__ . '/../app/Domain/Interfaces/UserRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TwitchUserService.php';
require_once __DIR__ . '/../app/Helpers/ResponseHelper.php';

return $app;
