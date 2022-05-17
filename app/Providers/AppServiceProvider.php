<?php

namespace App\Providers;

use App\Http\Generators\Commands\DockerImageCommand;
use App\Http\Generators\Commands\DockerServiceGenerationCommand;
use App\Http\Generators\Commands\JsonYmlConvertCommand;
use App\Http\Generators\Commands\OpenApiSpecificationCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands(DockerImageCommand::class);
        $this->commands(DockerServiceGenerationCommand::class);
        $this->commands(OpenApiSpecificationCommand::class);
        $this->commands(JsonYmlConvertCommand::class);
    }
}
