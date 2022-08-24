<?php

declare(strict_types=1);

namespace App\Http\Generators\Builders;

use App\Http\Generators\Builders\OpenApiHandlers\OpenApiSpecificationHandlerV30;

final class OpenApiSpecificationHandlerFactory
{
    public static function makeOpenApiSpecWithBuilder(string $version)
    {
        $handler = new OpenApiSpecificationHandlerV30;

        switch ($version) {

            case '3.1':
            case '3.1.0':
                // TODO
                break;

            default:
        }

        return $handler;
    }
}
