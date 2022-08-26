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
     */
    public function withPathContent(): self
    {
        $this->mockeryResponse->setPathContent(MocksHelper::getDataPathContent());

        return $this;
    }

    /**
     * @throws \App\Exceptions\GetServiceRouteException
     * @throws \App\Exceptions\ReferencePathException
     */
    public function withResponseBody(): self
    {
        $this->mockeryResponse->setResponseBodyContent(MocksHelper::getResponseBodyData());

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

        if ($headerRequestResponseType === 'schema' && isset($dataResponseContent['schema'])) {
            $dataResponseSchemaContent = $dataResponseContent['schema'];

            if (isset($dataResponseSchemaContent['$ref'])) {

                $responseDataBody = MocksHelper::getReferenceContent($dataResponseSchemaContent['$ref']);
                $this->mockeryResponse->setSchema(MocksHelper::returnResponseBodyWithEnvelope($responseDataBody));

                return $this;
            }

            $this->mockeryResponse->setSchema($dataResponseSchemaContent);
        }

        return $this;
    }

    public function build(): MockDataRequest
    {
        return new MockDataRequest($this->mockeryResponse);
    }
}
