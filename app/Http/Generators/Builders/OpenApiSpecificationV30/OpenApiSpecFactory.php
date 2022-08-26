<?php

declare(strict_types=1);

namespace App\Http\Generators\Builders\OpenApiSpecificationV30;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

final class OpenApiSpecFactory
{
    /**
     * @throws \App\Exceptions\ValidationException
     */
    public static function makeFileFromBuilder(OpenApiSpecBuilder $openApiSpecBuilder): void
    {
        $destinationTempPath = $openApiSpecBuilder->baseDirectoryOfJsonFiles.'/index.json';
        $openApiSpecificationRequest = $openApiSpecBuilder->build();

        $filesystem = new Filesystem;
        $filesystem->put($destinationTempPath, $openApiSpecificationRequest->toJson());

        $ymlDestinationPath = str_replace('.json', '.oas.yml', $destinationTempPath);
        Artisan::call("convert:files $destinationTempPath $ymlDestinationPath");
    }
}
