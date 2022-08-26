<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\GetServiceRouteException;
//use Illuminate\Http\Response;
use App\Exceptions\ReferencePathException;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

final class MocksHelper
{
    public static function getServiceRoutes(string $serviceName): array
    {
        try {
            $getSchemaJsonContent = MocksHelper::getSchemaService($serviceName);

        } catch (\Exception $e) {
            throw new GetServiceRouteException($e->getMessage());
        }

        $schemaArrayContent = json_decode($getSchemaJsonContent, true);
        if (is_null($schemaArrayContent)) {
            abort(ResponseAlias::HTTP_SERVICE_UNAVAILABLE,
                "THE SCHEMA FILE IS NOT VALID FOR THIS SERVICE $serviceName");
        }

        return $schemaArrayContent['paths'] ?? [];
//        Cache::remember($this->serviceName(), Carbon::now()->addDays(7), function(){
//
//        });
    }

    public static function getSchemaService($serviceName): string
    {
        $baseMockDirectory = base_path(config('fox_settings.base_directory'));
        $schemaFilePath = $baseMockDirectory.'/'.$serviceName.'/index.json';

        if (! file_exists($schemaFilePath)) {
            return '{}';
        }

        return File::get($schemaFilePath);
    }

    public static function getServiceName(): string
    {
        return request('service_name');
    }

    public static function requestUri(): string
    {
        $serviceName = MocksHelper::getServiceName();

        return substr(request()->path(), strlen($serviceName));
    }

    public static function requestMethod(): string
    {
        return strtolower(request()->method());
    }

    public static function getBaseDirectory(): string
    {
        return rtrim(config('fox_settings.base_directory', '/'), '/');
    }

    public static function headerRequestResponseType(): string
    {
        return strtolower(trim(request()->header('X-RESPONSE-TYPE') ?? 'DATA'));
    }

    public static function headerRequestEnvelope(): string|null
    {
        $envelope = request()->header('X-ENVELOPE-RESPONSE') ?? null;

        return $envelope ? strtolower(trim($envelope)) : null;
    }

    public static function headerRequestOverwriteContent(): string|null
    {
        return request()->header('X-OVERWRITE-CONTENT') ?? null;
    }

    public static function headerRequestStatusCode(): int
    {
        return request()->header('X-STATUS-CODE') ? (int) request()->header('X-STATUS-CODE') : ResponseAlias::HTTP_OK;
    }

    /**
     * @throws ReferencePathException
     */
    public static function getReferenceContent(string $referenceRoute): array
    {
        $referencePath = ltrim($referenceRoute, '/');
        $referencePath = getBaseServicePath().'/'.$referencePath;

        if (str_contains($referenceRoute, '#')) {
            $referencePath = ltrim(substr($referenceRoute, strpos($referenceRoute, '#') + 1), '/');
            $referencePath = getBaseServicePath().'/'.$referencePath;
        }
        $referencePath .= '.json';

        if (! file_exists($referencePath)) {
            throw new ReferencePathException('THE FILE WHICH YOU REFERENCED DOES NOT EXIST! <<'.$referencePath.'>>');
        }

        $getContent = json_decode(file_get_contents($referencePath), true);

        return MocksHelper::normalizeReferenceContentInNestedArray($getContent);
    }

    /**
     * @throws ReferencePathException
     */
    public static function normalizeReferenceContentInNestedArray(array $content): array
    {
        foreach ($content as $index => $value) {

            if (is_array($value) && isset($value['$ref'])) {
                $content[$index] = MocksHelper::getReferenceContent(end($value));
            }
        }

        return $content;
    }

    /**
     * @throws GetServiceRouteException
     */
    public static function getDataPathContent(string $requestUri = null): array
    {
        $serviceName = MocksHelper::getServiceName();
        $serviceRoutes = MocksHelper::getServiceRoutes($serviceName);
        $requestUri = $requestUri ?? MocksHelper::requestUri();
        $requestMethod = MocksHelper::requestMethod();

        if (isset($serviceRoutes[$requestUri])) {

            $requestResponseContent = $serviceRoutes[$requestUri];

            if (isset($requestResponseContent[$requestMethod])) {

                return $requestResponseContent[$requestMethod];
            }

            if (isset($requestResponseContent['default'])) {

                return $requestResponseContent['default'];
            }

            abort(405, 'METHOD NOT ALLOWED FOR THIS ROUTE!');
        }

        $pattern = '/{(?<=\{).*?(?=\})}/m';
        $listOfArrayWhichHasArguments = array_filter($serviceRoutes, function ($content, $path) use ($pattern) {
            preg_match($pattern, $path, $matches);

            return ! empty($matches);
        }, ARRAY_FILTER_USE_BOTH);

        if (! empty($listOfArrayWhichHasArguments)) {

            $bindingsArray = request()->route();
            $bindingsArray = $bindingsArray->parameters();
            unset($bindingsArray['service_name']);

            if (! empty($bindingsArray)) {

                $uriWithoutBindingValues = $requestUri;
                foreach ($bindingsArray as $keyPath => $value) {
                    if (str_contains($requestUri, $value)) {
                        $uriWithoutBindingValues = str_replace($value, '{'.$keyPath.'}', $uriWithoutBindingValues);
                    }
                }

                if ($uriWithoutBindingValues !== $requestUri) {
                    return MocksHelper::getDataPathContent($uriWithoutBindingValues);
                }
            }
        }

        return [];
    }

    /**
     * @throws GetServiceRouteException
     * @throws ReferencePathException
     */
    public static function getResponseBodyData(): array
    {
        $data = MocksHelper::getDataPathContent();
        $responseStatusCode = MocksHelper::headerRequestStatusCode() ?? ResponseAlias::HTTP_OK;
        $dataResponse = $data['responses'] ?? [];

        if (empty($dataResponse) || ! in_array($responseStatusCode, array_keys($data['responses']))) {
            return [];
        }

        $dataResponse = $dataResponse[$responseStatusCode];
        $dataResponseContent = $dataResponse['content']['application/json'] ?? [];

        return MocksHelper::normalizeReferenceContentInNestedArray($dataResponseContent);
    }

    public static function returnResponseBodyWithEnvelope(array $responseDataBody): array
    {
        $envelopeKey = MocksHelper::headerRequestEnvelope();
        if (! is_null($envelopeKey)) {
            return [$envelopeKey => $responseDataBody];
        }

        return $responseDataBody;
    }

    public static function submitDataValidation(array $data)
    {

//        $requestBodyContentSchema = $this->getRequestBodyContentSchema() ?? '{}';
//
//        $validator = new Validator;
//        $result = $validator->validate(Helper::toJSON($this->getAllBodyRequests()), $requestBodyContentSchema);
//
//        if ($result->isValid()) {
//            return true;
//        }
//
//        if ($result->hasError()) {
//            throw new ValidationException($result->error()->message(), $result->error());
//        }
    }
}
