<?php

declare(strict_types=1);

namespace App\Http\Generators\Builders\OpenApiSpecificationV30;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

final class OpenApiSpecFactory
{
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    /**
     * @throws \App\Exceptions\ValidationException
     */
    public static function makeFileFromBuilder(OpenApiSpecBuilder $openApiSpecBuilder): void
    {
        $jsonDestinationPath = $openApiSpecBuilder->baseDirectoryOfJsonFiles.'/route.json';
        $openApiSpecificationRequest = $openApiSpecBuilder->build();

        $selfOpenApiSpecFactory = (new OpenApiSpecFactory);
        $selfOpenApiSpecFactory->filesystem->put($jsonDestinationPath, $openApiSpecificationRequest->toJson());
        $selfOpenApiSpecFactory->generateOpenApiSpecificationFiles($openApiSpecBuilder);
    }

    /**
     * @throws \App\Exceptions\ValidationException
     */
    private function generateOpenApiSpecificationFiles(OpenApiSpecBuilder $openApiSpecBuilder): void
    {
        $serviceName = $openApiSpecBuilder->getServiceName();
        $openApiSpecificationRequestArray = $openApiSpecBuilder->build()->toArray();
        $normalizedPath = [];

        foreach ($openApiSpecificationRequestArray as $index => $value) {
            if ($index == 'paths') {

                foreach ($value as $path => $pathValue) {
                    $normalizedPath['/'.$serviceName.$path] = $pathValue;
                }
            }
        }
        $openApiSpecificationRequestArray['paths'] = $normalizedPath;

        $jsonDestinationPath = $openApiSpecBuilder->baseDirectoryOfJsonFiles.'/index.oas.json';
        $this->filesystem->put($jsonDestinationPath, json_encode($openApiSpecificationRequestArray, JSON_PRETTY_PRINT));

        $ymlDestinationPath = str_replace('.json', '.yml', $jsonDestinationPath);
        Artisan::call("convert:files $jsonDestinationPath $ymlDestinationPath");
    }
}
