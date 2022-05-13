<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class ServiceCheckingMiddleware
{
    public function handle($request, Closure $next)
    {
        $this->isBaseMocksDirectoryExist();
        $this->isServiceMocksDirectoryExists();
        
        return $next($request);
    }

    private function isBaseMocksDirectoryExist()
    {
        $baseMockDirectory = base_path(config('settings.base_dir'));

        if (!is_dir($baseMockDirectory)) {
            throw new \Exception('There is no mocks directory! please after creating your mocks directory define it in the settings configuration.');
        }
    }

    private function isServiceMocksDirectoryExists(): void
    {
        $baseMockDirectory = base_path(config('settings.base_dir'));
        $requiredFields = config('settings.required_service_fields');
        $availableServices = config('services');

        foreach ($availableServices as $service_name => $service_data) {

            if (isset($service_data['active']) && $service_data['active'] == false){
                continue;
            }

            if (!empty(array_diff($requiredFields, array_keys($service_data))) && !isset($service_data['base_directory'])){
                dump("This service $service_name doesn't have all of the required service fields, so it'll ignore!");
                Log::warning("This service $service_name doesn't have all of the required service fields, so it'll ignore!");
                continue;
            }

            if (!is_dir($baseMockDirectory . '/' . $service_data['base_directory'])){
                throw new \Exception("There is no mocks directory for $service_name, so you can de-activate the service in the service configuration file.");
            }
        }

    }
}
