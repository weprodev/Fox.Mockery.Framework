<?php

return [
    'base_dir' => 'mocks/services',     // the location of the mocks files

    'required_service_fields' => [
        'prefix',
        'port',
    ],

    'docker' => [
        'version' => '3.9',
        'path' => 'deployment',
        'image_path' => 'deployment/images',
    ],

];
