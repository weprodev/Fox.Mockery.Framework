<?php

declare(strict_types=1);

namespace App\Services\MockData;

use App\Http\Controllers\MocksHelper;
use App\ValueObjects\MockeryResponse;

final class MockDataBuilder
{
    private MockeryResponse $mockeryResponse;

    public function __construct()
    {
        $this->mockeryResponse = new MockeryResponse;
    }

    /**
     * @throws \App\Exceptions\GetServiceRouteException
     * @throws \App\Exceptions\ReferencePathException
     */
    public function withPathContent(): self
    {
        $getPathContent = MocksHelper::getDataPathContent();
        $normalizePathContent = MocksHelper::normalizeReferenceContentInNestedArray($getPathContent);
        $this->mockeryResponse->setPathContent($normalizePathContent);

        return $this;
    }

    /**
     * @throws \App\Exceptions\GetServiceRouteException
     * @throws \App\Exceptions\ReferencePathException
     */
    public function withResponseBody(): self
    {
        $getResponseBody = MocksHelper::getResponseBodyData();
        $normalizeResponseBody = MocksHelper::normalizeReferenceContentInNestedArray($getResponseBody);
        $this->mockeryResponse->setResponseBodyContent($normalizeResponseBody);

        return $this;
    }

    public function withExample(): self
    {
        //TODO prepare example
        return $this;
    }

    /**
     * @throws \App\Exceptions\ReferencePathException
     * @throws \App\Exceptions\GetServiceRouteException
     */
    public function withSchema(): self
    {
        $dataResponseContent = MocksHelper::getResponseBodyData();
        $headerRequestResponseType = MocksHelper::headerRequestResponseType();

        $dataResponseSchemaContent = [];
        if ($headerRequestResponseType === 'schema' && isset($dataResponseContent['schema'])) {

            $dataResponseSchemaContent = MocksHelper::normalizeReferenceContentInNestedArray($dataResponseContent['schema']);
        }

        $this->mockeryResponse->setSchema($dataResponseSchemaContent);

        return $this;
    }

    public function build(): MockDataRequest
    {
        return new MockDataRequest($this->mockeryResponse);
    }
}
