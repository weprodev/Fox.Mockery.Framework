<?php

declare(strict_types=1);

namespace App\Services\MockData;

final class MockDataFactory
{
    public static function generateResponseFromRequestObject(MockDataRequest $mockDataRequest): MockDataResponse
    {
        return new MockDataResponse($mockDataRequest);
    }
}
