<?php

namespace App\Http\Generators;

use App\Http\Generators\Builders\OpenApiSpecificationHandlerFactory;
use App\Http\Generators\Builders\OpenApiSpecificationV30\OpenApiSpecFactory;

class OpenApiSpecificationGenerator extends Generator
{
    protected string $stub = 'openapis/openapi';

    protected string $name;

    private string $version;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->name = $this->getServiceName();
        $this->version = $this->getOpenApiSpecVersion();
    }

    public function getPathConfigNode(): string
    {
        return 'openapis';
    }

    public function getDestinationPathGeneratedFile(): string
    {
        return $this->getBasePath() . '/' .
            parent::getConfigGeneratorPath($this->getPathConfigNode(), true) . '/' .
            $this->getName() . '/index.oas3.yml';
    }

    /**
     * @throws \App\Exceptions\FileDoesNotExistException
     * @throws \App\Exceptions\ValidationException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function run(): int
    {
        $openApiHandler = OpenApiSpecificationHandlerFactory::makeOpenApiSpecWithBuilder($this->version);
        $openApiBuilder = $openApiHandler->generateOpenApiSpecification($this->getServiceName(), $this->version);
        OpenApiSpecFactory::makeFileFromBuilder($openApiBuilder);

        return true;
    }

    public function getServiceName(): string
    {
        return strtolower($this->options['service']);
    }

    public function getOpenApiSpecVersion(): string
    {
        return $this->options['version'] ?? config('fox_settings.openapi.default', '3.0');
    }
}
