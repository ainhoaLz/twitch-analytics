<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Http\Controllers;

$router->get('/analytics/user', [
    'uses' => 'GetUserController',
]);

$router->get('/analytics/streams', [
    'uses' => 'GetStreamsController',
]);
$router->get('/analytics/streams/enriched', [
    'uses' => 'GetEnrichedStreamsController',
]);
$router->get('/analytics/topsofthetops', [
    'uses' => 'GetTopsOfTheTopsController',
]);
$router->post('/register', [
    'uses' => 'RegisterController',
]);
$router->post('/token', [
    'uses' => 'TokenController',
]);
