<?php

declare(strict_types=1);

namespace App\Http\Generators\Builders\OpenApiSpecificationV30;

use App\Exceptions\ValidationException;
use App\ValueObjects\OpenApiSpecV30;

final class OpenApiSpecRequest
{
    private OpenApiSpecV30 $openApiSpec;

    /**
     * @throws ValidationException
     */
    public function __construct(OpenApiSpecV30 $openApiSpec)
    {
        $this->openApiSpec = $openApiSpec;
        $this->validation();
    }

    public function toArray(): array
    {
        return $this->normalizeData();
    }

    public function toJson(): string
    {
        return json_encode($this->normalizeData(), JSON_PRETTY_PRINT);
    }

    public function toObject(): OpenApiSpecV30
    {
        return $this->openApiSpec;
    }

    private function getRequiredFields(): array
    {
        $requiredFields = [
            'openapi',
            'info',
            'paths',
        ];

        return config('fox_openapis.3_0.required_items') ?? $requiredFields;
    }

    private function getFixedFields(): array
    {
        $fixedFields = [
            'openapi',
            'info',
            'servers',
            'paths',
            'components',
            'security',
            'tags',
            'externalDocs',
        ];

        return config('fox_openapis.3_0.fixed_fields') ?? $fixedFields;
    }

    private function validation(): void
    {
        foreach ($this->getRequiredFields() as $field) {
            $fieldName = ucfirst(strtolower($field));
            $getFieldValue = $this->openApiSpec->{'get'.$fieldName}();

            if (is_null($getFieldValue) || (is_array($getFieldValue) && empty($getFieldValue))) {
                throw new ValidationException('There is no value for required fields << '.$field.' >>!');
            }
        }
    }

    private function normalizeData(): array
    {
        $normalizeData = [];
        foreach ($this->getFixedFields() as $field) {
            $fieldName = ucfirst(strtolower($field));
            $getFieldValue = $this->openApiSpec->{'get'.$fieldName}();

            if (is_array($getFieldValue) && empty($getFieldValue)) {
                continue;
            }

            $normalizeData[$field] = $getFieldValue;
        }

        return $normalizeData;
    }
}
