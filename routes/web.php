<?php

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/mrsstest', 'GenerateXmlController@create');

$router->group(['prefix' => 'api/'], function () use ($router) {
    $router->post('login', 'Auth\LoginController@login');

    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->post('refresh', 'Auth\LoginController@refresh');
        $router->post('logout', 'Auth\LoginController@logout');

        $router->get('mrss', 'MrssController@index');
        $router->post('mrss', 'MrssController@store');
        $router->get('mrss/{mrss}', 'MrssController@info');
        $router->put('mrss/{mrss}', 'MrssController@update');
        $router->delete('mrss/{mrss}', 'MrssController@destroy');
        $router->put('mrss/{mrss}/action', 'MrssController@action');
        $router->get('mrss/{mrss}/entries', 'MrssController@entries');
        $router->get('mrss/{mrss}/entry/{entry}', 'MrssController@entry');

        $router->get('settings', 'SettingController@get');
        $router->post('settings', 'SettingController@store');
    });
});
