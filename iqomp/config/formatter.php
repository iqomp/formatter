<?php

return [
    'formats' => [
        'std-name' => [
            'name' => [
                'type' => 'text'
            ]
        ]
    ],
    'handlers' => [
        'boolean' => [
            'handler' => 'Iqomp\\Formatter\\Handler::boolean',
            'collective' => FALSE
        ],
        'bool' => [
            'handler' => 'Iqomp\\Formatter\\Handler::boolean',
            'collective' => FALSE
        ],
        'clone' => [
            'handler' => 'Iqomp\\Formatter\\Handler::clone',
            'collective' => FALSE
        ],
        'custom' => [
            'handler' => 'Iqomp\\Formatter\\Handler::custom',
            'collective' => FALSE
        ],
        'date' => [
            'handler' => 'Iqomp\\Formatter\\Handler::date',
            'collective' => FALSE
        ],
        'delete' => [
            'handler' => 'Iqomp\\Formatter\\Handler::delete',
            'collective' => FALSE
        ],
        'embed' => [
            'handler' => 'Iqomp\\Formatter\\Handler::embed',
            'collective' => FALSE
        ],
        'interval' => [
            'handler' => 'Iqomp\\Formatter\\Handler::interval',
            'collective' => FALSE
        ],
        'multiple-text' => [
            'handler' => 'Iqomp\\Formatter\\Handler::multipleText',
            'collective' => FALSE
        ],
        'number' => [
            'handler' => 'Iqomp\\Formatter\\Handler::number',
            'collective' => FALSE
        ],
        'text' => [
            'handler' => 'Iqomp\\Formatter\\Handler::text',
            'collective' => FALSE
        ],
        'json' => [
            'handler' => 'Iqomp\\Formatter\\Handler::json',
            'collective' => FALSE
        ],
        'join' => [
            'handler' => 'Iqomp\\Formatter\\Handler::join',
            'collective' => FALSE
        ],
        'rename' => [
            'handler' => 'Iqomp\\Formatter\\Handler::rename',
            'collective' => FALSE
        ],
        'std-id' => [
            'handler' => 'Iqomp\\Formatter\\Handler::stdId',
            'collective' => FALSE
        ],
        'switch' => [
            'handler' => 'Iqomp\\Formatter\\Handler::switch',
            'collective' => FALSE
        ]
    ]
];
