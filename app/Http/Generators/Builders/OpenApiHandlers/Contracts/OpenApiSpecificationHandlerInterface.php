<?php

namespace App\Http\Generators\Builders\OpenApiHandlers\Contracts;

interface OpenApiSpecificationHandlerInterface
{
    public function generateOpenApiSpecification(string $serviceName);
}
