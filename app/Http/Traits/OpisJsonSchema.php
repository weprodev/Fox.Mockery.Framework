<?php

namespace App\Http\Traits;

use App\Exceptions\GetServiceRouteException;
use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

trait OpisJsonSchema
{
    protected $data;
    protected int $responseStatusCode;
    protected int|null $requestedStatusCodeResponse;


    public function getServiceRoutes(): array
    {
        try {
            $getSchemaJsonContent = getSchemaService($this->serviceName());
        } catch (\Exception $e) {
            throw new GetServiceRouteException($e->getMessage());
        }

        $schemaArrayContent = json_decode($getSchemaJsonContent, true);
        if (is_null($schemaArrayContent)) {
            abort(Response::HTTP_SERVICE_UNAVAILABLE, "THE SCHEMA FILE IS NOT VALID FOR THIS SERVICE {$this->serviceName()}");
        }

        return $schemaArrayContent['paths'];
//        Cache::remember($this->serviceName(), Carbon::now()->addDays(7), function(){
//
//        });
    }


    public function getSchema(): ?string
    {
        if (in_array($this->requestMethod(), ['get', 'delete'])) {
            return null;
        }

        $this->validateAndSetRequestBodyData();
        return $this->getRequestBodyContentSchema();
    }


    public function getResponseContent(): ?string
    {
        $this->validateAndSetRequestBodyData();
        $responseBody = $this->getResponseBodyContentSchema($this->requestedStatusCodeResponse ?? null);
        if (is_null($responseBody)) {
            $this->responseStatusCode = Response::HTTP_NO_CONTENT;
        }
        return $responseBody;
    }


    public function validateAndSetRequestBodyData()
    {
        if ($this->settingDataResponse()) {
            return;
        }

        $listOfArrayWhichHasArguments = array_filter($this->getServiceRoutes(), function ($content, $path) {
            preg_match('/{(?<=\{).*?(?=\})}/m', $path, $matches);
            return !empty($matches);
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($listOfArrayWhichHasArguments) && $this->checkingRoutesWithArgumentBinding()) {
            return;
        }

        abort(Response::HTTP_NOT_FOUND, "THE REQUESTED URI DOES NOT EXIST!");
    }


    public function dataValidation(array $data)
    {
        if ($this->requestBodyIsRequired($data) && empty($this->getAllBodyRequests())) {
            abort(400, 'Request Body Is Required!');
        }

        $requestBodyContentSchema = $this->getRequestBodyContentSchema() ?? "{}";

        $validator = new Validator();
        $result = $validator->validate(Helper::toJSON($this->getAllBodyRequests()), $requestBodyContentSchema);

        if ($result->isValid()) {
            return true;
        }

        if ($result->hasError()) {
            throw new ValidationException($result->error()->message(), $result->error());
        }
    }


    public function __get($method)
    {
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new \Exception("$method DOES NOT EXIST!");
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE FUNCTIONS
    |--------------------------------------------------------------------------
    |
    */

    private function checkingRoutesWithArgumentBinding(): bool
    {
        $bindingsArray = request()->route();
        $bindingsArray = end($bindingsArray);
        unset($bindingsArray['service_name']);
        unset($bindingsArray['any']);

        if (empty($bindingsArray)) {
            return false;
        }

        $uriWithoutBindingValues = $this->requestUri();
        foreach ($bindingsArray as $keyPath => $value) {
            if (str_contains($this->requestUri(), $value)) {
                $uriWithoutBindingValues = str_replace($value, '{' . $keyPath . '}', $uriWithoutBindingValues);
            }
        }

        if ($uriWithoutBindingValues !== $this->requestUri()) {
            return $this->settingDataResponse($uriWithoutBindingValues);
        }

        return false;
    }


    private function settingDataResponse(string $requestUri = null): bool
    {
        $serviceRoutes = $this->getServiceRoutes();
        $requestUri = $requestUri ?? $this->requestUri();

        if (isset($serviceRoutes[$requestUri])) {
            $requestRouteContent = $serviceRoutes[$requestUri];

            if (isset($requestRouteContent[$this->requestMethod()])) {

                $data = $requestRouteContent[$this->requestMethod()];
                $this->data = $data;
                $this->dataValidation($data);
                return true;
            }

            if (isset($requestRouteContent['default'])) {
                $this->data = $requestRouteContent['default'];
                return true;
            }

            abort(405, "METHOD NOT ALLOWED FOR THIS ROUTE!");
        }

        return false;
    }


    private function requestBodyIsRequired(array $data): bool
    {
        if (!isset($data['requestBody']) || empty($data['requestBody'])) {
            return false;
        }

        return $data['requestBody']['required'] ?? false;
    }


    private function serviceName(): string
    {
        return request('service_name');
    }


    private function serviceUrl(): string
    {
        return url() . '/' . $this->serviceName();
    }


    private function requestUri(): string
    {
        return substr(request()->path(), strlen($this->serviceName()));
    }


    private function requestMethod(): string
    {
        return strtolower(request()->method());
    }


    private function getAllBodyRequests(string $key = null): array
    {
        return request()->all($key);
    }


    private function getAllHeaderRequests(string $key = null): array
    {
        return request()->header($key);
    }


    private function getRequestBodyContentSchema(): string|null
    {
        if (!isset($this->data['requestBody']['content']['application/json']['schema'])) {
            return null;
        }

        return json_encode($this->data['requestBody']['content']['application/json']['schema'], JSON_PRETTY_PRINT);
    }


    private function getResponseBodyContentSchema(int $status = null): string|null
    {
        if ($status && !isset($this->data['responses']["$status"])) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, "THERE IS NO RESPONSE FOR {$status} STATUS CODE!");
        }

        $status = $status ?? (request('X-STATUS-CODE') ?? Response::HTTP_OK);
        return match (true) {
            (Response::HTTP_OK == $status) => $this->checkToReturnHttpOkResponse(),
            (Response::HTTP_NOT_FOUND == $status) => $this->checkToReturnHttpNotFoundResponse(),
            (!is_null($status)) => $this->checkToReturnHttpOkResponse(null, $status),
            default => null,
        };
    }


    private function checkToReturnHttpOkResponse(string $type = null, int $statusCode = null): string|null
    {
        $this->responseStatusCode = $statusCode ?? Response::HTTP_OK;

        if (!in_array(Response::HTTP_OK, array_keys($this->data['responses']))) {
            if (empty($this->data['responses'])) {
                $this->responseStatusCode = Response::HTTP_NO_CONTENT;
                return null;
            }

            $this->responseStatusCode = key(end($this->data));
        }

        $responseContent = $this->data['responses'][$this->responseStatusCode];
        $responseContent = $responseContent['content']['application/json'] ?? null;

        $type = !in_array($type, ['schema', 'examples']) ? null : $type;

        if ($type == 'schema' && isset($responseContent['schema'])) {
            return json_encode($responseContent['schema'], JSON_PRETTY_PRINT);
        }

        if ($type == 'examples' && isset($responseContent['examples'])) {
            return json_encode($responseContent['examples'], JSON_PRETTY_PRINT);
        }

        if (isset($responseContent['response'])) {
            return json_encode(['response' => $responseContent['response']], JSON_PRETTY_PRINT);
        }

        return $responseContent ? json_encode($responseContent, JSON_PRETTY_PRINT) : null;
    }


    private function checkToReturnHttpNotFoundResponse(): string|null
    {
        $this->responseStatusCode = Response::HTTP_NOT_FOUND;
        $responseContent = $this->data['responses'][$this->responseStatusCode];
        $responseContent = $responseContent['content']['application/json'] ?? null;

        if (isset($responseContent['response'])) {
            return json_encode(['response' => $responseContent['response']], JSON_PRETTY_PRINT);
        }

        return $responseContent ? json_encode($responseContent, JSON_PRETTY_PRINT) : null;
    }

}
