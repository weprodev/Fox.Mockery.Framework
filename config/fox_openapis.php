<?php

return [

    '3_0' => [

        // fox_openapis.3_0.fixed_fields
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

        // fox_openapis.3_0.required_items
        'required_fields' => [
            'openapi',
            'info',
            'paths',
        ],

    ],

    //    'fields' => [
    //        'openapi' => 'required|string',
    //
    //        'info' => [
    //            'version' => 'required|string',
    //            'title' => 'required|string',
    //            'summary' => 'nullable|string',
    //            'description' => 'nullable',
    //            'termsOfService' => 'nullable|url',
    //
    //            'license' => 'nullable',
    //            'license.name' => 'required|string',
    //            'license.identifier' => 'nullable',
    //            'license.url' => 'nullable|url',
    //
    //            'contact' => 'nullable',
    //            'contact.name' => 'nullable|string',
    //            'contact.url' => 'nullable|url',
    //            'contact.email' => 'nullable|email',
    //        ],
    //
    //        'servers' => [
    //            'url' => 'required|url',
    //            'description' => 'nullable|string',
    //        ],
    //
    //        'paths' => [
    //            '$ref' => 'nullable|string',
    //            'summary' => 'nullable|string',
    //            'description' => 'nullable|string',
    //            'parameters' => 'nullable',
    //            'servers' => 'nullable|array',
    //
    //            'get' => 'nullable',
    //            'put' => 'nullable',
    //            'post' => 'nullable',
    //            'delete' => 'nullable',
    //            'options' => 'nullable',
    //            'head' => 'nullable',
    //            'patch' => 'nullable',
    //            'trace' => 'nullable',
    //        ],
    //
    //        'components' => [
    //            'tags' => 'nullable',
    //            'schemas' => 'nullable',
    //        ],
    //    ],
];
