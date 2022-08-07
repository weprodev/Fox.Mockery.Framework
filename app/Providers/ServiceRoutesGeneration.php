<?php

namespace App\Providers;

use App\Http\Controllers\MocksController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class ServiceRoutesGeneration extends ServiceProvider
{
    private array $routeMethods;

    public function __construct()
    {
        $this->routeMethods = ['get', 'post', 'put', 'delete', 'patch', 'options'];
    }

    public function generateRoutes()
    {
        foreach (getAvailableServices() as $serviceName => $service) {

            $serviceDocsPath = '{service_name}/'.config('fox_settings.service_docs_prefix');
            Route::get($serviceDocsPath, [MocksController::class, 'serviceDocumentation']);

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

                return ! empty($matches);
            }, ARRAY_FILTER_USE_BOTH);

            $this->generateDynamicRoutes($listOfArrayWithArguments, 'indexWithArguments');

            $routesWithoutVariables = array_diff_key($listOfPaths, $listOfArrayWithArguments);
            $this->generateDynamicRoutes($routesWithoutVariables);
        }

    }

    private function generateDynamicRoutes($routes, $action = 'index')
    {
        foreach ($routes as $path => $content) {
            foreach ($content as $method => $pathContent) {
                if (! in_array($method, $this->routeMethods)) {
                    continue;
                }

                $routePath = '{service_name}/'.ltrim($path, '/');
                Route::{$method}($routePath, [MocksController::class, $action]);
            }
        }
    }

    private function settingGeneralServicesRoutes()
    {
        foreach ($this->routeMethods as $method) {
            Route::prefix('{service_name}')->controller(MocksController::class)
                ->group(function () use ($method) {
                    Route::{$method}('/', 'index');
                    // Route::{$method}('/{any}', 'index')->where('any', '.*');
                });
        }
    }

    private function settingDefaultRoutes()
    {
        foreach ($this->routeMethods as $method) {
            Route::{$method}('/', 'App\Http\Controllers\Controller@default');
        }
    }
}
