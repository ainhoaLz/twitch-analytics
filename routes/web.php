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
use App\Controllers;

$router->get('/analytics/user', [
    'uses' => 'GetUserById\GetUserByIdController',
]);

$router->get('/analytics/streams', [
    'uses' => 'GetStreams\GetStreamsController',
]);
$router->get('/analytics/streams/enriched', [
    'uses' => 'GetEnrichedStreams\GetEnrichedStreamsController',
]);
$router->get('/analytics/topsofthetops', [
    'uses' => 'GetTopsOfTheTops\GetTopsOfTheTopsController',
]);
$router->post('/register', [
    'uses' => 'Register\RegisterController',
]);
$router->post('/token', [
    'uses' => 'Token\TokenController',
]);
