<?php

namespace App\Exceptions;

use Exception;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\JsonPointer;

class ValidationException extends Exception
{
    private $details;

    public function __construct(string $exception_message, $details = null)
    {
        parent::__construct($exception_message);
        $this->details = $details;
    }

    public function getDetails()
    {
        return $this->normalizeErrorResponse();
    }

    private function normalizeErrorResponse()
    {
        $error = $this->details;
        $formatter = new ErrorFormatter();

        $normalizeErrorResponse = function (ValidationError $error) use ($formatter) {
            $schema = $error->schema()->info();

            return [
                'schema' => [
                    'id' => $schema->id(),
                    'base' => $schema->base(),
                    'root' => $schema->root(),
                    'draft' => $schema->draft(),
                    'path' => JsonPointer::pathToFragment($schema->path()),
                    'contents' => $schema->data(),
                    // see Opis\JsonSchema\Info\SchemaInfo for more properties
                ],
                'error' => [
                    'keyword' => $error->keyword(),
                    'args' => $error->args(),
                    'message' => $error->message(),
                    'formattedMessage' => $formatter->formatErrorMessage($error),
                ],
                'data' => [
                    'type' => $error->data()->type(),
                    'value' => $error->data()->value(),
                    'fullPath' => $error->data()->fullPath(),
                ],
            ];
        };

        $normalizeKeys = function (ValidationError $error): string {
            return implode('.', $error->data()->fullPath());
        };

        return $formatter->format($error, false, $normalizeErrorResponse, $normalizeKeys)[""] ??
            $formatter->format($error, false, $normalizeErrorResponse, $normalizeKeys);
    }

}
