<?php

return [
    'base_directory' => 'mocks/services',

    'version' => '3.1.0',   // Enabled version

    'fields' => [

        'main' => [
            'openapi' => 'required|string',
            'info' => 'required|array',
            'servers' => 'nullable|array',
            'paths' => 'nullable|array',
        ],

        '3_1_0' => [

            'openapi' => 'required|string',

            'info' => [
                'version' => 'required|string',
                'title' => 'required|string',
                'summary' => 'nullable|string',
                'description' => 'nullable',
                'termsOfService' => 'nullable|url',

                'license' => 'nullable',
                'license.name' => 'required|string',
                'license.identifier' => 'nullable',
                'license.url' => 'nullable|url',

                'contact' => 'nullable',
                'contact.name' => 'nullable|string',
                'contact.url' => 'nullable|url',
                'contact.email' => 'nullable|email',
            ],

            'servers' => [
                'url' => 'required|url',
                'description' => 'nullable|string',

                'variables' => 'nullable',
                'variables.enum' => 'nullable',     // The array MUST NOT be empty.
                'variables.default' => 'required|string',
                'variables.description' => 'nullable|string',
            ],

            'paths' => [
                '$ref' => 'nullable|string',
                'summary' => 'nullable|string',
                'description' => 'nullable|string',
                'parameters' => 'nullable',     // The list MUST NOT include duplicated parameters
                'servers' => 'nullable|array',  // An alternative server array to service all operations in this path.
                'get' => 'nullable',
                'put' => 'nullable',
                'post' => 'nullable',
                'delete' => 'nullable',
                'options' => 'nullable',
                'head' => 'nullable',
                'patch' => 'nullable',
                'trace' => 'nullable',
            ],
            'components' => [
                'tags' => 'nullable',
                'schemas' => 'nullable'
            ]
        ]
    ]
];
