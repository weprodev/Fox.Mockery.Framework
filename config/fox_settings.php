<?php

return [

    // fox_settings.base_directory
    'base_directory' => 'mocks/services',     // the location of the mocks files

    // fox_settings.service_docs_prefix
    'service_docs_prefix' => 'docs',    // for showing list of the routes for each service

    'response' => [
        'status' => true,
        // fox_settings.response.type
        'type' => 'EXAMPLE_AND_OVERWRITE', // EXAMPLE, EXAMPLE_AND_OVERWRITE, SCHEMA
    ],

    // fox_settings.required_service_fields
    'required_service_fields' => [
        'prefix',
        'port',
    ],

    // fox_settings.available_status_codes
    'available_status_codes' => [
        200,
        201,
        204,
        400,
        401,
        404,
        500,
        503,
    ],

    'openapi' => [

        // fox_settings.openapi.default
        'default' => '3.0.3',

        // fox_settings.openapi.status
        'status' => true,

        // fox_settings.openapi.fields
        'fixed_fields' => [
            'openapi',
            'info',
            'servers',
            'paths',
            'components',
            'security',
            'tags',
            'externalDocs',
        ],

    ],

    // fox_settings.json_schema_fields
    'json_schema_fields' => [
        'info' => 'required',
        'servers' => 'nullable',
        'paths' => 'nullable',
        'components' => 'nullable',
    ],

    // fox_settings.docker
    'docker' => [
        'version' => '3.9',
        'path' => 'deployment',
        'image_path' => 'deployment/images',
    ],

];