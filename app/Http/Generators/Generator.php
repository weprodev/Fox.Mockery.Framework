<?php

namespace App\Http\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class Generator
{
    protected Filesystem $filesystem;
    protected array $options;
    protected string $stub;
    protected string $baseDirectory;


    public function __construct(array $options = [])
    {
        $this->filesystem = new Filesystem;
        $this->options = $options;
        $this->baseDirectory = rtrim(config('fox_settings.base_directory', '/'), '/');
    }

    abstract public function getPathConfigNode();

    abstract public function getDestinationPathGeneratedFile(): string;


    public function getStub(): string
    {
        $stubPath = __DIR__ . '/Stubs/' . $this->stub . '.stub';

        return (new Stub($stubPath, $this->getReplacements()))->render();
    }


    public function getBasePath(): string
    {
        return rtrim(base_path(), '/');
    }


    public function getBaseServicePath(): string
    {
        $baseServicePath = $this->getBasePath() . '/' . $this->baseDirectory . '/' . $this->getServiceName();
        return rtrim($baseServicePath, '/');
    }


    public function getName(): string
    {
        $name = strtolower($this->name);

        if (Str::contains($name, '\\')) {
            $name = str_replace('\\', '/', $name);
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '/', $name);
        }

        return str_replace(' ', '/', ucwords(str_replace('/', ' ', $name)));
    }


    public function setUp(): void
    {
        // Setup some hook...
    }


    public function run(): int
    {

        $this->setUp();
        $path = $this->getDestinationPathGeneratedFile();

        if ($this->filesystem->exists($path) && !$this->force) {
            dump("File already exist($path), if you want to overwrite it add -f option in your command!");
            return false;
        }

        if (!$this->filesystem->isDirectory($dir = dirname($path))) {

            $this->filesystem->makeDirectory($dir, 0777, true, true);
        }

        return $this->filesystem->put($path, $this->getStub());
    }


    public function getReplacements(): array
    {
        return [];
    }


    public function getOptions(): array
    {
        return $this->options;
    }


    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }


    public function getOption(string $key, string $default = null): null|string
    {
        if (!$this->hasOption($key)) {
            return $default;
        }

        return $this->options[$key] ?: $default;
    }


    public function option(string $key, string $default = null): null|string
    {
        return $this->getOption($key, $default);
    }


    public function getConfigGeneratorPath(string $entity, $directoryPath = false): string
    {
        $path = match ($entity) {
            'docker' => config('fox_settings.docker.image_path', 'deployment/images'),
            'openapis', 'json-schema' => config('fox_settings.base_directory', 'mocks/services'),
            default => '',
        };

        if ($directoryPath) {
            $path = str_replace('\\', '/', $path);
        } else {
            $path = str_replace('/', '\\', $path);
        }

        return rtrim($path, '/');
    }


    public function __get(string $key): null|string
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->option($key);
    }

    protected function mergingJsonFilesInDirectory(string $pathDirectory): string
    {
        $jsonPathsFiles = scanDirectoryAndReturnFiles($pathDirectory, '.json');

        $jsonContent = json_encode([]);
        foreach ($jsonPathsFiles as $jsonFile) {
            $filePath = $pathDirectory . '/' . $jsonFile;
            $jsonContent = mergingTwoJsonFile($jsonContent, file_get_contents($filePath));
        }

        return $jsonContent;
    }
}
