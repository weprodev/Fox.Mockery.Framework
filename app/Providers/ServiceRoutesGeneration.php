<?php

namespace App\Providers;

use Illuminate\Http\Response;

class ServiceRoutesGeneration
{
    private array $routeMethods;

    public function __construct()
    {
        $this->routeMethods = ['get', 'post', 'put', 'delete', 'patch', 'options'];
    }

    public function generateRoutes()
    {
        foreach (getAvailableServices() as $serviceName => $service) {

            $this->settingBindingServicesRoutes($serviceName);

            $this->settingGeneralServicesRoutes();
        }

        $this->settingDefaultRoutes();
    }

    private function settingBindingServicesRoutes($serviceName)
    {
        $getSchemaJsonContent = getSchemaService($serviceName);

        $schemaArrayContent = json_decode($getSchemaJsonContent, true);
        if (is_null($schemaArrayContent)) {
            abort(Response::HTTP_SERVICE_UNAVAILABLE, "THE SCHEMA FILE IS NOT VALID FOR THIS SERVICE $serviceName");
        }

        if (isset($schemaArrayContent['paths'])) {

            $listOfPaths = $schemaArrayContent['paths'];
            $listOfArrayWithArguments = array_filter($listOfPaths, function ($content, $path) {
                preg_match('/{(?<=\{).*?(?=\})}/m', $path, $matches);
                return !empty($matches);
            }, ARRAY_FILTER_USE_BOTH);

            foreach ($listOfArrayWithArguments as $path => $content) {
                foreach ($content as $method => $pathContent) {
                    if (!in_array($method, $this->routeMethods)){
                        continue;
                    }

                    $routePath = '{service_name}/' . ltrim($path, '/');
                    app()->router->{$method}($routePath, 'App\Http\Controllers\MocksController@indexWithArguments');
                }
            }
        }

    }

    private function settingGeneralServicesRoutes()
    {
        foreach ($this->routeMethods as $method) {
            app()->router->{$method}('{service_name}', 'App\Http\Controllers\MocksController@index');
            app()->router->{$method}('{service_name}/{any:.*}', 'App\Http\Controllers\MocksController@index');
        }
    }

    private function settingDefaultRoutes()
    {
        foreach ($this->routeMethods as $method) {
            app()->router->{$method}('/', 'App\Http\Controllers\Controller@default');
        }
    }

}
