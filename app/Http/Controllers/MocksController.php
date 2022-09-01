<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ServiceIsNotValidException;
use App\Services\MockData\MockDataBuilder;
use App\Services\MockData\MockDataFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MocksController extends Controller
{
    private string $responseType;

    private string|null $envelope;

    private bool $overwrite;

    private int|null $requestedStatusCodeResponse;

    /**
     * @throws ServiceIsNotValidException
     * @throws \App\Exceptions\GetServiceRouteException
     */
    public function __construct()
    {
        $this->serviceNameValidation();
        $this->initialize();
    }

    private function initialize()
    {
        $this->responseType = MocksHelper::headerRequestResponseType();
        $this->envelope = MocksHelper::headerRequestEnvelope();
        $this->overwrite = MocksHelper::headerRequestOverwriteContent();
        $this->requestedStatusCodeResponse = MocksHelper::headerRequestStatusCode();
    }

    /**
     * @throws \App\Exceptions\GetServiceRouteException
     * @throws \App\Exceptions\ReferencePathException
     * @throws \App\Exceptions\ValidationException
     */
    public function index(): JsonResponse
    {
        MocksHelper::submitDataValidation();

        $mockDataBuilder = new MockDataBuilder;
        $mockDataBuilder = $mockDataBuilder
            ->withPathContent()
            ->withResponseBody()
            ->withSchema()
            ->withExample()
            ->build();

        $mockDataResponse = MockDataFactory::generateResponseFromRequestObject($mockDataBuilder);

        return $mockDataResponse->getJsonResponseData();
    }

    /**
     * @throws \App\Exceptions\ReferencePathException
     * @throws \App\Exceptions\ValidationException
     * @throws \App\Exceptions\GetServiceRouteException
     */
    public function indexWithArguments(...$arguments): JsonResponse
    {
        return $this->index();
    }

    public function serviceDocumentation(Request $request, string $service_name)
    {
        $docs = MocksHelper::getServiceContent($service_name);

        if ($request->wantsJson()) {
            return response()->json($docs);
        }

        $docs = json_decode($docs, true);

        return view('service-docs', compact('docs', 'service_name'));
    }

    private function serviceNameValidation()
    {
        $serviceName = request('service_name');
        $isServiceValid = in_array($serviceName, array_keys(getAvailableServices()));

        if (! $isServiceValid) {
            throw new ServiceIsNotValidException('The service is not Valid! check your service config file.');
        }
    }
}
