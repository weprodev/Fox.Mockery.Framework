<?php

namespace App\Http\Controllers;


use http\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MocksController extends Controller
{
    private string $requestUri;
    private string $requestMethod;
    private string $serviceName;
    private array $serviceConfig;
    private string $serviceUrl;

    public function __construct()
    {
        $this->requestMethod = ucfirst(strtolower(request()->method()));
        $this->serviceName = request('service_name');

        $this->redirectToHomeIfServiceIsNotValid();

        $this->serviceConfig = getServiceConfig($this->serviceName);
        $servicePort = $this->serviceConfig['port'];
        $this->serviceUrl = "http://localhost:{$servicePort}";

        $this->checkingIsServiceDockerContainerIsAvailable();

        $baseUrl = url('/') . "/{$this->serviceName}";
        $this->requestUri = ltrim(substr(request()->getUri(), strlen($baseUrl)), '/');
    }

    public function index(Request $request): JsonResponse
    {
        $responseBody = $this->{"sendHttp{$this->requestMethod}Request"}($request);
        return response()->json(json_decode($responseBody, true));
    }

    private function redirectToHomeIfServiceIsNotValid(): void
    {
        if (!in_array($this->serviceName, array_keys(getAvailableServices()))) {
            header("Location: " . url('/'));
            exit;
        }
    }

    private function checkingIsServiceDockerContainerIsAvailable(): void
    {
        try {
            Http::get($this->serviceUrl);
        } catch (\Exception $exception) {
            $response = [
                'message' => "SERVICE << $this->serviceName >> IS NOT AVAILABLE!",
                'service' => array_merge($this->serviceConfig, [
                    'url' => $this->serviceUrl
                ]),
                'details' => $exception->getMessage(),
            ];
            dd($response);
        }
    }

    private function sendHttpGetRequest(Request $request): string
    {
        $response = Http::get("{$this->serviceUrl}/{$this->requestUri}");
        return $response->body();
    }


}
