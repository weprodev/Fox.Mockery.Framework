<?php

namespace App\Http\Generators;

use Illuminate\Filesystem\Filesystem;

class DockerGenerator extends Generator
{
    protected string $stub = 'docker/service-docker-image';
    private string $compose_stub = 'docker/service-docker-compose';

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->name = $this->getServiceName();
    }

    public function getPathConfigNode(): string
    {
        return 'docker';
    }

    /**
     * Get destination path for generated file.
     */
    public function getPath(): string
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorPath($this->getPathConfigNode(), true) . '/' . $this->getName() . 'Image.yml';
    }

    public function getReplacements(): array
    {
        return [
            'SERVICE_NAME' => $this->getServiceName(),
            'SERVICE_OPEN_API_SPECIFICATION_DIR' => $this->getOpenApiSpecificationDirectory(),
            'PORT' => $this->getServicePort()
        ];
    }

    public function getServiceName(): string
    {
        return $this->options['service'];
    }

    public function getOpenApiSpecificationDirectory(): string
    {
        return config('setting.base_dir', 'mocks/services');
    }

    public function getServicePort(): int
    {
        return $this->options['port'];
    }

    public function regeneratingDockerComposeFile()
    {
        $serviceDockerContent = $this->generateServiceContent();
        $dockerComposeStub = __DIR__ . '/Stubs/docker/docker-compose.stub';
        $dockerComposeDestinationPath = $this->getBasePath() . '/' . config('settings.docker.path') . '/' . 'docker-compose.yml';

        $dockerComposeStubReplacement = [
            'DOCKER_VERSION' => config('settings.docker.version'),
            'SERVICES' => $serviceDockerContent
        ];
        $dockerComposeContent = (new Stub($dockerComposeStub, $dockerComposeStubReplacement))->render();

        $composeFileSystem = new Filesystem;
        return $composeFileSystem->put($dockerComposeDestinationPath, $dockerComposeContent);
    }

    private function generateServiceContent(): string
    {
        $serviceComposeStubPath = __DIR__ . '/Stubs/' . $this->compose_stub . '.stub';
        $availableServices = array_filter(config("services"), function ($service) {
            return $service['active'];
        });
        $contents = "";

        foreach (array_keys($availableServices) as $service_name) {

            $composeStubReplacement = [
                'SERVICE_NAME' => $service_name,
                'SERVICE_IMAGE_NAME' => ucfirst(strtolower($service_name)) . 'Image.yml',
            ];
            $contents .= (new Stub($serviceComposeStubPath, $composeStubReplacement))->render();
            $contents .= "\n";
        }

        return $contents;
    }
}
