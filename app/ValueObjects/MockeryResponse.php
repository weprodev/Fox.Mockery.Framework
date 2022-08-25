<?php

declare(strict_types=1);

namespace App\ValueObjects;

final class MockeryResponse
{
    private array $pathContent;

    private array $responseBodyContent;

    public function getResponseBodyContent(): array
    {
        return $this->responseBodyContent;
    }

    public function setResponseBodyContent(array $responseBodyContent): void
    {
        $this->responseBodyContent = $responseBodyContent;
    }

    public function getPathContent(): array
    {
        return $this->pathContent;
    }

    public function setPathContent(array $pathContent): void
    {
        $this->pathContent = $pathContent;
    }

}
