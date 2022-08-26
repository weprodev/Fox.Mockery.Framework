<?php

declare(strict_types=1);

namespace App\Services\MockData;

use App\Http\Controllers\MocksHelper;
use App\ValueObjects\MockeryResponse;
use Illuminate\Http\JsonResponse;

final class MockDataRequest
{
    private MockeryResponse $mockeryResponse;

    public function __construct(MockeryResponse $mockeryResponse)
    {
        $this->mockeryResponse = $mockeryResponse;
    }

    public function toArray(): array
    {
        return $this->mockeryResponse->getAllDataAsAnArray();
    }

    public function toJson(): JsonResponse
    {
        return jsonResponse(
            $this->mockeryResponse->getAllDataAsAnArray(),
            MocksHelper::headerRequestStatusCode());
    }
}
