<?php

declare(strict_types=1);

namespace App\Http\Generators\Builders\OpenApiHandlers;

use App\Http\Generators\Builders\OpenApiHandlers\Contracts\OpenApiSpecificationHandlerInterface;
use App\Http\Generators\Builders\OpenApiSpecificationV30\OpenApiSpecBuilder;

final class OpenApiSpecificationHandlerV30 implements OpenApiSpecificationHandlerInterface
{
    /**
     * @throws \App\Exceptions\FileDoesNotExistException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function generateOpenApiSpecification(string $serviceName, string $version = null): OpenApiSpecBuilder
    {
        $openApiBuilder = new OpenApiSpecBuilder($serviceName);

        return $openApiBuilder
            ->withVersion($version ?? config('fox_settings.openapi.default', '3.0.0'))
            ->withInfo()
            ->withPaths()
            ->withComponents()
            ->withTags()
            ->withSecurity()
            ->withServers()
            ->withExternalDocs();
    }
}
