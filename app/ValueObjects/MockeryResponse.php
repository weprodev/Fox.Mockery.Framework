<?php

declare(strict_types=1);

namespace App\ValueObjects;

final class MockeryResponse
{
    private array $pathContent;

    private array $responseBodyContent;

    private array $schema;

    private array $examples;

    public function getResponseBodyContent(): array
    {
        return $this->responseBodyContent ?? [];
    }

    public function setResponseBodyContent(array $responseBodyContent): void
    {
        $this->responseBodyContent = $responseBodyContent;
    }

    public function getPathContent(): array
    {
        return $this->pathContent ?? [];
    }

    public function setPathContent(array $pathContent): void
    {
        $this->pathContent = $pathContent;
    }

    public function setSchema(array $schemaContent): void
    {
        $this->schema = $schemaContent;
    }

    public function getSchema(): array
    {
        return $this->schema ?? [];
    }

    public function setExample(array $exampleContent): void
    {
        $this->examples = $exampleContent;
    }

    public function getExample(): array
    {
        return $this->examples ?? [];
    }

    public function getAllDataAsAnArray(): array
    {
        $data['pathContent'] = $this->getPathContent();
        $data['responseBodyContent'] = $this->getResponseBodyContent();
        $data['schema'] = $this->getSchema();
        $data['examples'] = $this->getExample();

        return $data;
    }
}
