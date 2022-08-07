<?php

namespace App\Http\Controllers;

use App\Http\Traits\OpisJsonSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MocksController extends Controller
{
    use OpisJsonSchema;

    protected mixed $response;

    protected ?string $schema;

    protected string $responseType;

    protected string|null $envelope;

    protected string|null $overwrite;

    public function __construct()
    {
        $this->responseType = strtoupper(trim(request()->header('X-RESPONSE-TYPE') ?? 'DATA'));
        $this->envelope = strtolower(trim(request()->header('X-ENVELOPE-RESPONSE'))) ?? null;
        $this->overwrite = request()->header('X-OVERWRITE-CONTENT') ?? null;
        $this->requestedStatusCodeResponse = request()->header('X-STATUS-CODE') ?? null;

        $this->schema = $this->getSchema();
        $this->response = json_decode($this->getResponseContent(), true);
    }

    public function index(): JsonResponse
    {
        if ($this->responseStatusCode === Response::HTTP_NO_CONTENT) {
            return jsonResponse([], $this->responseStatusCode);
        }

        if ($this->responseType == 'EXAMPLE') {
            return $this->returnExampleResponse();
        }

        return $this->returnDataResponse();
    }

    public function indexWithArguments(...$arguments): JsonResponse
    {
        return $this->index();
    }

    public function serviceDocumentation(Request $request, $service_name)
    {
        $docs = getSchemaService($service_name);

        if ($request->wantsJson()) {
            return response()->json($docs);
        }

        $docs = json_decode($docs, true);
        return view('service-docs', compact('docs', 'service_name'));
    }



    private function returnDataResponse(): JsonResponse
    {
        $data = [
            'schema' => json_decode($this->schema, true),
            'examples' => $this->response['examples'] ?? [],
        ];

        $data = $this->generateResponseData($data);

        if ($this->envelope) {
            return $this->returnResponseByEnvelope($data);
        }

        return jsonResponse($data, $this->responseStatusCode);
    }

    private function returnExampleResponse(): JsonResponse
    {
        $examples = $this->response['examples'] ?? [];

        if (empty($examples)) {
            return $this->returnDataResponse();
        }

        $example = end($examples);
        if ($this->overwrite) {
            $example = $this->overWriteExampleResponse($example);
        }

        if ($this->envelope) {
            return $this->returnResponseByEnvelope($example);
        }

        return jsonResponse($example, $this->responseStatusCode);
    }

    private function returnResponseByEnvelope(array $responseBody): JsonResponse
    {
        if ($this->envelope != 'null') {
            $responseBody = [$this->envelope => $responseBody];
        }

        return jsonResponse($responseBody, $this->responseStatusCode);
    }

    private function overWriteExampleResponse($example): array
    {
        if (isset($example[0])) {

            foreach (request()->all() as $key => $value) {
                $value = explode(',', request('primary_color'));

                foreach ($value as $index => $replacement) {

                    if (isset($example[$index]) && trim($replacement) != '') {
                        $example[$index][$key] = trim($replacement);
                    }
                }

            }

            return $example;
        }

        return array_merge($example, request()->all());
    }

    private function generateResponseData(array $data): array
    {

        if (isset($this->response['response'])) {
            return $this->response['response'];
        }

        if (config('fox_settings.response.status')) {

            $examples = $this->response['examples'] ?? [];

            $response = match (strtoupper(config('fox_settings.response.type'))) {
                'EXAMPLE' => empty($examples) ? [] : end($examples),
                'EXAMPLE_AND_OVERWRITE' => $this->overWriteExampleResponse((empty($examples) ? [] : end($examples))),
                'SCHEMA' => json_decode($this->schema, true),
                default => null,
            };

            if ($response) {
                $data['response'] = $response;
            }
        }

        return $data;
    }
}
