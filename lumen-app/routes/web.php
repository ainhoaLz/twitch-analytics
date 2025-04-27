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

$router->get('/analytics/user', 'Controller@getUsers');
$router->get('/analytics/streams', 'Controller@getStreams');
$router->get('/analytics/streams/enriched', 'Controller@getEnrichedStreams');
$router->get('/analytics/topsofthetops', 'Controller@getTopsofthetops');
$router->post('/register', 'Controller@registerUser');
$router->post('/token', 'Controller@generateToken');
