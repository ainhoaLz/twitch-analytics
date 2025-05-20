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
    \App\Domain\Interfaces\Database\TokenRepositoryInterface::class,
    function () {
        return new \App\Infrastructure\Repositories\Database\TokenRepository(
            \App\Infrastructure\DatabaseConnection::connect()
        );
    }
);

$app->singleton(
    \App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface::class,
    function ($app) {
        return new \App\Infrastructure\Repositories\Database\TwitchTokenRepository(
            \App\Infrastructure\DatabaseConnection::connect()
        );
    }
);

$app->singleton(
    \App\Infrastructure\DatabaseConnection::class,
    function () {
        return new \App\Infrastructure\DatabaseConnection();
    }
);

$app->singleton(
    \App\Domain\Interfaces\Database\UserRepositoryInterface::class,
    function ($app) {
        return new \App\Infrastructure\Repositories\Database\UserRepository(
            $app->make(\App\Infrastructure\DatabaseConnection::class)
        );
    }
);

$app->singleton(
    \App\Domain\Interfaces\Database\TopGamesRepositoryInterface::class,
    function ($app) {
        return new \App\Infrastructure\Repositories\Database\TopGamesRepository(
            $app->make(\App\Infrastructure\DatabaseConnection::class)
        );
    }
);

$app->singleton(
    \App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class,
    function ($app) {
        return new \App\Infrastructure\Repositories\TwitchAPI\TwitchTokenRepository(
            $app->make(\App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface::class)
        );
    }
);


$app->singleton(
    \App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface::class,
    function ($app) {
        $clientId = getenv('TWITCH_CLIENT_ID');
        $clientSecret = getenv('TWITCH_CLIENT_SECRET');

        $tokenService = new \App\Application\Services\TwitchTokenService(
            $app->make(\App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface::class),
            $app->make(\App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class)
        );

        $accessToken = $tokenService->getToken($clientId, $clientSecret);

        return new \App\Infrastructure\Repositories\TwitchAPI\TwitchUserRepository(
            $accessToken,
            $clientId
        );
    }
);

$app->singleton(
    \App\Domain\Interfaces\TwitchAPI\TwitchStreamRepositoryInterface::class,
    function ($app) {
        $clientId = getenv('TWITCH_CLIENT_ID');
        $clientSecret = getenv('TWITCH_CLIENT_SECRET');

        $tokenService = new \App\Application\Services\TwitchTokenService(
            $app->make(\App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface::class),
            $app->make(\App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class)
        );

        $accessToken = $tokenService->getToken($clientId, $clientSecret);

        return new \App\Infrastructure\Repositories\TwitchAPI\TwitchStreamRepository(
            $accessToken,
            $clientId
        );
    }
);

$app->singleton(
    \App\Domain\Interfaces\TwitchAPI\TopsofthetopsRepositoryInterface::class,
    function ($app) {
        $clientId = getenv('TWITCH_CLIENT_ID');
        $clientSecret = getenv('TWITCH_CLIENT_SECRET');

        $tokenService = new \App\Application\Services\TwitchTokenService(
            $app->make(\App\Domain\Interfaces\Database\TwitchTokenRepositoryInterface::class),
            $app->make(\App\Domain\Interfaces\TwitchAPI\TwitchTokenRepositoryInterface::class)
        );

        $accessToken = $tokenService->getToken($clientId, $clientSecret);

        return new \App\Infrastructure\Repositories\TwitchAPI\TopsofthetopsRepository(
            $accessToken,
            $clientId
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

require_once __DIR__ . '/../app/Domain/Interfaces/Database/TopGamesRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TwitchEnrichedStreamService.php';
require_once __DIR__ . '/../app/Application/Services/TwitchStreamService.php';
require_once __DIR__ . '/../app/Application/Services/TwitchTokenService.php';
require_once __DIR__ . '/../app/Domain/Interfaces/Database/TwitchTokenRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TokenService.php';
require_once __DIR__ . '/../app/Domain/Interfaces/Database/TokenRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TopsofthetopsService.php';
require_once __DIR__ . '/../app/Application/Services/RegisterService.php';
require_once __DIR__ . '/../app/Domain/Interfaces/Database/UserRepositoryInterface.php';
require_once __DIR__ . '/../app/Application/Services/TwitchUserService.php';
require_once __DIR__ . '/../app/Infrastructure/DatabaseConnection.php';

return $app;
