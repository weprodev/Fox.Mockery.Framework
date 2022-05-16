<?php

return [
    'base_dir' => 'mocks/services',

    'required_service_fields' => [
        'prefix',
        'port',
    ],

    'docker' => [
        'version' => '3.9',
        'path' => 'deployment',
        'image_path' => 'deployment/images',
    ]

];
