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
            $getServiceJsonContent = MocksHelper::getServiceContent($serviceName);

        } catch (\Exception $e) {
            throw new GetServiceRouteException($e->getMessage());
        }

        $serviceArrayContent = json_decode($getServiceJsonContent, true);
        if (is_null($serviceArrayContent)) {
            abort(ResponseAlias::HTTP_SERVICE_UNAVAILABLE,
                "THE SCHEMA FILE IS NOT VALID FOR THIS SERVICE $serviceName");
        }

        return $serviceArrayContent['paths'] ?? [];

        //TODO CACHE ...
    }

    public static function getServiceContent($serviceName): string
    {
        $baseMockDirectory = base_path(config('fox_settings.base_directory'));
        $serviceFileRoutePath = $baseMockDirectory.'/'.$serviceName.'/route.json';

        if (! file_exists($serviceFileRoutePath)) {
            return '{}';
        }

        return File::get($serviceFileRoutePath);
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
        return strtolower(trim(request()->header('X-RESPONSE-TYPE') ??
            config('fox_settings.default_response_type', 'ALL')));
    }

    public static function headerRequestEnvelope(): string|null
    {
        $envelope = request()->header('X-ENVELOPE-RESPONSE') ?? null;

        return $envelope ? strtolower(trim($envelope)) : null;
    }

    public static function headerRequestOverwriteContent(): bool
    {
        return (bool) request()->header('X-OVERWRITE-CONTENT');
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

            if (is_array($value)) {
                $content[$index] = self::normalizeReferenceContentInNestedArray($value);
            }

            if ($index == '$ref') {
                return MocksHelper::getReferenceContent($value);
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

        return $dataResponse['content']['application/json'] ?? [];
    }

    public static function returnResponseBodyWithEnvelope(array $responseDataBody): array
    {
        $envelopeKey = MocksHelper::headerRequestEnvelope();
        if (! is_null($envelopeKey)) {
            return [$envelopeKey => $responseDataBody];
        }

        return $responseDataBody;
    }

    public static function submitDataValidation(): void
    {

//        $requestSchemaContent = MocksHelper::getServiceContent(MocksHelper::getServiceName());

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
