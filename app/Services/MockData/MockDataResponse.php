<?php

declare(strict_types=1);

namespace App\Services\MockData;

use App\Exceptions\ResponseTypeException;
use App\Http\Controllers\MocksHelper;
use Illuminate\Http\JsonResponse;

final class MockDataResponse
{
    private MockDataRequest $mockDataRequest;

    private array $responseBodyContent;

    private array $pathContent;

    private array $schema;

    private array $example;

    private array $responseHeader;

    private int $responseStatusCode;

    private string $responseType;

    private string|null $envelope;

    private bool $overwriteContent;

    /**
     * @throws ResponseTypeException
     */
    public function __construct(MockDataRequest $mockDataRequest)
    {
        $this->mockDataRequest = $mockDataRequest;
        $this->envelope = MocksHelper::headerRequestEnvelope();
        $this->responseType = MocksHelper::headerRequestResponseType();
        $this->responseStatusCode = MocksHelper::headerRequestStatusCode();
        $this->overwriteContent = MocksHelper::headerRequestOverwriteContent();

        $this->validateResponseType();
        $this->setResponseHeader();
        $this->setResponseBodyContent();
        $this->setPathContent();
        $this->setSchema();
        $this->setExample();
    }

    public function getArrayResponseData(): array
    {
        return $this->prepareResponseContent();
    }

    public function getJsonResponseData(): JsonResponse
    {
        return jsonResponse(
            $this->prepareResponseContent(),
            $this->getResponseStatusCode(),
            $this->getResponseHeader()
        );
    }

    public function getResponseBodyContent(): array
    {
        if ($this->envelope) {
            return MocksHelper::returnResponseBodyWithEnvelope($this->responseBodyContent);
        }

        return $this->responseBodyContent;
    }

    public function getPathContent(): array
    {
        if ($this->envelope) {
            return MocksHelper::returnResponseBodyWithEnvelope($this->pathContent);
        }

        return $this->pathContent;
    }

    public function getSchema(): array
    {
        if ($this->envelope) {
            return MocksHelper::returnResponseBodyWithEnvelope($this->schema);
        }

        return $this->schema;
    }

    public function getExample(): array
    {
        if ($this->envelope) {
            return MocksHelper::returnResponseBodyWithEnvelope($this->example);
        }

        return $this->example;
    }

    public function getOverwriteExample(): array
    {
        if ($this->envelope) {
            return MocksHelper::returnResponseBodyWithEnvelope($this->example);
        }

        return $this->example;
    }

    public function getResponseStatusCode(): int
    {
        return $this->responseStatusCode;
    }

    public function getResponseHeader(): array
    {
        return $this->responseHeader ?? [];
    }

    private function setResponseBodyContent(): void
    {
        $this->responseBodyContent = $this->mockDataRequest->toArray()['responseBodyContent'] ?? [];
    }

    private function setPathContent(): void
    {
        $this->pathContent = $this->mockDataRequest->toArray()['pathContent'];
    }

    private function setSchema(): void
    {
        $this->schema = $this->mockDataRequest->toArray()['schema'] ?? [];
    }

    private function setExample(): void
    {
        $this->example = $this->mockDataRequest->toArray()['example'] ?? [];
    }

    public function setResponseHeader(): void
    {
        $this->responseHeader = [
            'X-ENVELOPE-RESPONSE' => $this->envelope ?? 'NULL',
            'X-OVERWRITE-CONTENT' => $this->overwriteContent ? 'TRUE' : 'FALSE',
            'X-STATUS-CODE' => (string) $this->getResponseStatusCode(),
            'X-RESPONSE-TYPE' => strtoupper($this->responseType).
                ' |  VALID_TYPES: '.implode(', ', $this->getValidResponseTypes()),
        ];
    }

    /**
     * @throws ResponseTypeException
     */
    private function validateResponseType()
    {
        if (! in_array(strtoupper($this->responseType), $this->getValidResponseTypes())) {
            throw new ResponseTypeException(
                'THE RESPONSE TYPE IS NOT VALID! VALID ITEMS:'.implode(',', $this->getValidResponseTypes())
            );
        }
    }

    private function getValidResponseTypes(): array
    {
        return [
            'EXAMPLE',
            'EXAMPLE_AND_OVERWRITE',
            'SCHEMA',
            'BODY',
            'ALL',
        ];
    }

    private function prepareResponseContent(): array
    {
        return match (strtoupper($this->responseType)) {
            'EXAMPLE' => $this->getExample(),
            'EXAMPLE_AND_OVERWRITE' => $this->getOverwriteExample(),
            'SCHEMA' => $this->getSchema(),
            'BODY' => $this->getResponseBodyContent(),
            'ALL' => $this->getPathContent(),
        };
    }
}
