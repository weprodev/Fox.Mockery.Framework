<?php

namespace App\Http\Generators;

class Stub
{
    protected static null|string $basePath = null;

    protected string $path;

    protected array $replaces = [];

    public function __construct($path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }

    public static function create(string $path, array $replaces = []): self
    {
        return new static($path, $replaces);
    }

    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    public function replace(array $replaces = []): Stub
    {
        $this->replaces = $replaces;

        return $this;
    }

    public function getReplaces(): array
    {
        return $this->replaces;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function render(): string
    {
        return $this->getContents();
    }

    public function getContents(): null|string
    {
        $contents = file_get_contents($this->getStubPath());
        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$' . strtoupper($search) . '$', $replace, $contents);
        }

        return $contents;
    }

    public function getStubPath(): string
    {
        return static::$basePath . $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
