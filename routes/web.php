<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$routeMethods = ['get', 'post', 'put', 'delete', 'patch', 'options', 'head'];

foreach ($routeMethods as $method) {
    $router->{$method}('/', 'Controller@default');
}

foreach (getAvailableServices() as $service_name => $service) {

    $router->group(['prefix' => '/'], function () use ($service_name, $service, $router, $routeMethods) {

        foreach ($routeMethods as $method) {
            $router->{$method}('{service_name}', 'MocksController@index');
            $router->{$method}('{service_name}/{any:.*}', 'MocksController@index');
        }
    });
}

