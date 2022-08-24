<?php

declare(strict_types=1);

namespace App\ValueObjects;

final class OpenApiSpecV30
{
    private string $openApi;

    private array $info;

    private array $paths;

    private array $servers;

    private array $components;

    private array $security;

    private array $tags;

    private array $externalDocs;

    public function setOpenApi(string $version)
    {
        $this->openApi = $version;
    }

    public function setInfo(array $info): void
    {
        $this->info = $info;
    }

    public function setPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    public function setServers(array $servers): void
    {
        $this->servers = $servers;
    }

    public function setComponents(array $components): void
    {
        $this->components = $components;
    }

    public function setSecurity(array $security): void
    {
        $this->security = $security;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function setExternalDocs(array $externalDocs): void
    {
        $this->externalDocs = $externalDocs;
    }

    public function getOpenapi(): string
    {
        return $this->openApi;
    }

    public function getInfo(): array
    {
        return $this->info ?? [];
    }

    public function getPaths(): array
    {
        return $this->paths ?? [];
    }

    public function getSpecificPaths(string $path): array
    {
        //TODO
        return $this->paths ?? [];
    }

    public function getServers(): array
    {
        return $this->servers ?? [];
    }

    public function getComponents(): array
    {
        return $this->components ?? [];
    }

    public function getSpecificComponents(string $component): array
    {
        //TODO
        return $this->components ?? [];
    }

    public function getSecurity(): array
    {
        return $this->security ?? [];
    }

    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    public function getExternalDocs(): array
    {
        return $this->externalDocs ?? [];
    }
}
