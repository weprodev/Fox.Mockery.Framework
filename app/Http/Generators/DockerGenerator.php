<?php

namespace App\Http\Generators;

use Illuminate\Filesystem\Filesystem;

class DockerGenerator extends Generator
{
    protected string $stub = 'docker/service-docker-image';
    protected string $name;
    private string $composeStub = 'docker/service-docker-compose';


    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->name = $this->getServiceName();
    }


    public function getPathConfigNode(): string
    {
        return 'docker';
    }


    public function getDestinationPathGeneratedFile(): string
    {
        return $this->getBasePath() . '/' .
            parent::getConfigGeneratorPath($this->getPathConfigNode(), true) . '/' .
            $this->getName() . 'Image.yml';
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
        return config('fox_setting.base_dir', 'mocks/services');
    }


    public function getServicePort(): int
    {
        return $this->options['port'];
    }


    public function regeneratingDockerComposeFile(): bool|int
    {
        $serviceDockerContent = $this->generateServiceContent();
        $dockerComposeStub = __DIR__ . '/Stubs/docker/docker-compose.stub';
        $dockerComposeDestinationPath = $this->getBasePath() . '/' . config('fox_settings.docker.path') . '/' . 'docker-compose.yml';

        $dockerComposeStubReplacement = [
            'DOCKER_VERSION' => config('fox_settings.docker.version'),
            'SERVICES' => $serviceDockerContent
        ];
        $dockerComposeContent = (new Stub($dockerComposeStub, $dockerComposeStubReplacement))->render();

        $composeFileSystem = new Filesystem;
        return $composeFileSystem->put($dockerComposeDestinationPath, $dockerComposeContent);
    }


    private function generateServiceContent(): string
    {
        $serviceComposeStubPath = __DIR__ . '/Stubs/' . $this->composeStub . '.stub';
        $availableServices = getAvailableServices();
        $contents = "";

        foreach (array_keys($availableServices) as $serviceName) {

            $composeStubReplacement = [
                'SERVICE_NAME' => $serviceName,
                'SERVICE_IMAGE_NAME' => ucfirst(strtolower($serviceName)) . 'Image.yml',
            ];
            $contents .= (new Stub($serviceComposeStubPath, $composeStubReplacement))->render();
            $contents .= "\n";
        }

        return $contents;
    }

}
