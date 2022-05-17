<?php

namespace App\Http\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class Generator
{
    protected Filesystem $filesystem;
    protected array $options;
    protected string $stub;

    public function __construct(array $options = [])
    {
        $this->filesystem = new Filesystem;
        $this->options = $options;
    }

    abstract public function getPathConfigNode();
    abstract function getPath(): string;

    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    public function setFilesystem(Filesystem $filesystem): Generator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    public function getStub(): string
    {
        $stubPath = __DIR__ . '/Stubs/' . $this->stub . '.stub';

        return (new Stub($stubPath, $this->getReplacements()))->render();
    }

    public function getBasePath(): string
    {
        return base_path();
    }

    public function getName(): string
    {
        $name = $this->name;
        if (Str::contains($this->name, '\\')) {
            $name = str_replace('\\', '/', $this->name);
        }
        if (Str::contains($this->name, '/')) {
            $name = str_replace('/', '/', $this->name);
        }

        return strtolower(Str::studly(str_replace(' ', '/', ucwords(str_replace('/', ' ', $name)))));
    }

    public function setUp(): void
    {
        // Setup some hook...
    }


    public function run(): int
    {
        $this->setUp();
        $path = $this->getPath();

        if ($this->filesystem->exists($path) && !$this->force) {
            dump("File already exist($path), if you want to overwrite it add -f option in your command!");
            return true;
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


    public function getConfigGeneratorPath(string $entity, $directoryPath = false)
    {
        switch ($entity) {

            case ('docker' === $entity):
                $path = config('settings.docker.image_path', 'deployment/images');
                break;

            case ('openapis' === $entity):
                $path = config('openapis.base_directory', 'mocks/services');
                break;

            default:
                $path = '';
        }

        if ($directoryPath) {
            $path = str_replace('\\', '/', $path);
        } else {
            $path = str_replace('/', '\\', $path);
        }

        return $path;
    }


    public function __get(string $key): null|string
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->option($key);
    }
}
