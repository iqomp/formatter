<?php

return [
    'formats' => [
        'std-test' => [
            'age' => [
                'type' => 'number'
            ],
            'name' => [
                'type' => 'text'
            ]
        ],
        'std-type-not-exists' => [
            'age' => [
                'type' => 'non-exists-type'
            ]
        ],
        'std-rest' => [
            '@rest' => [
                'type' => 'number'
            ],
            'name' => [
                'type' => 'text'
            ]
        ],
        'std-rename' => [
            'user_name' => [
                'type' => 'text',
                '@rename' => 'name'
            ]
        ],
        'std-clone' => [
            'name' => [
                'type' => 'text'
            ],
            'fullname' => [
                'type' => 'text',
                '@clone' => 'name'
            ]
        ]
    ]
];
