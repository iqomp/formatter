<?php

/**
 * Config provider
 * @package iqomp/formatter
 * @version 2.0.0
 */

namespace Iqomp\Formatter;

class ConfigProvider
{
    protected function getPublishedFiles(): array
    {
        $base = dirname(__DIR__) . '/publish';
        $files = $this->scanDir($base, '/');
        $result = [];

        foreach ($files as $file) {
            $source = $base . $file;
            $target = BASE_PATH . $file;

            $result[] = [
                'id' => $file,
                'description' => 'Publish file of ' . $file,
                'source' => $source,
                'destination' => $target
            ];
        }

        return $result;
    }

    protected function scanDir(string $base, string $path): array
    {
        $base_path = chop($base . $path, '/');
        $files = array_diff(scandir($base_path), ['.', '..']);
        $result = [];

        foreach ($files as $file) {
            $file_path = trim($path . '/' . $file, '/');
            $file_base = $base_path . '/' . $file;

            if (is_dir($file_base)) {
                $sub_files = $this->scanDir($base, '/' . $file_path);
                if ($sub_files) {
                    $result = array_merge($result, $sub_files);
                }
            } else {
                $result[] = '/' . $file_path;
            }
        }

        return $result;
    }

    public function __invoke()
    {
        return [
            'publish' => $this->getPublishedFiles(),
            'formatter' => [
                'handlers' => [
                    'boolean' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::boolean',
                        'collective' => false
                    ],
                    'bool' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::boolean',
                        'collective' => false
                    ],
                    'clone' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::clone',
                        'collective' => false
                    ],
                    'custom' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::custom',
                        'collective' => false
                    ],
                    'date' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::date',
                        'collective' => false
                    ],
                    'delete' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::delete',
                        'collective' => false
                    ],
                    'embed' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::embed',
                        'collective' => false
                    ],
                    'interval' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::interval',
                        'collective' => false
                    ],
                    'multiple-text' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::multipleText',
                        'collective' => false
                    ],
                    'number' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::number',
                        'collective' => false
                    ],
                    'text' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::text',
                        'collective' => false
                    ],
                    'json' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::json',
                        'collective' => false
                    ],
                    'join' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::join',
                        'collective' => false
                    ],
                    'rename' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::rename',
                        'collective' => false
                    ],
                    'std-id' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::stdId',
                        'collective' => false
                    ],
                    'switch' => [
                        'handler' => 'Iqomp\\Formatter\\Handler::switch',
                        'collective' => false
                    ]
                ]
            ]
        ];
    }
}
