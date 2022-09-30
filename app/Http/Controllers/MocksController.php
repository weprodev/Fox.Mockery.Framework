<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\GetServiceRouteException;
use App\Exceptions\ReferencePathException;
use App\Exceptions\ServiceIsNotValidException;
use App\Exceptions\ValidationException;
use App\Services\MockData\MockDataBuilder;
use App\Services\MockData\MockDataFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
     * @throws GetServiceRouteException
     * @throws ReferencePathException
     * @throws ValidationException
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
     * @throws ReferencePathException
     * @throws ValidationException
     * @throws GetServiceRouteException
     */
    public function indexWithArguments(...$arguments): JsonResponse
    {
        return $this->index();
    }

    public function serviceDocumentation(Request $request, string $service_name): View|Factory|JsonResponse|Application
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
