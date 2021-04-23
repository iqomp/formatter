<?php

/**
 * Object formatter
 * @package iqomp/formatter
 * @version 2.0.0
 */

namespace Iqomp\Formatter;

class Formatter
{

    protected static function propAsKey(array $array, string $prop): array
    {
        $res = [];
        foreach ($array as $arr) {
            $key = is_array($arr) ? $arr[$prop] : $arr->$prop;
            if (is_object($key)) {
                $key = (string)$key;
            }
            $res[$key] = $arr;
        }

        return $res;
    }

    public static function format(
        string $format,
        object $object,
        array $options = []
    ): ?object {

        $result = self::formatMany($format, [$object], $options);
        if (!$result) {
            return null;
        }

        return $result[0];
    }

    public static function formatApply(
        array $formats,
        array $objects,
        array $options = [],
        string $askey = null
    ): ?array {

        if (isset($formats['@rest'])) {
            foreach ($objects as $object) {
                foreach ($object as $prop => $val) {
                    if (!isset($formats[$prop])) {
                        $formats[$prop] = $formats['@rest'];
                    }
                }
                break;
            }
            unset($formats->{'@rest'});
        }

        $handlers = config('formatter.handlers');
        $collective_data = [];

        // 1. Group properties by collectivity type.
        //  0 => non collective
        //  1 => collective
        // 1.a clone @clone-ed property
        $collectives = [[],[]];
        foreach ($formats as $field => $opts) {
            if (isset($opts['@clone'])) {
                foreach ($objects as $index => &$object) {
                    $object->$field = $object->{$opts['@clone']};
                }
                unset($object);
            }

            $type    = $opts['type'];
            if (!isset($handlers[$type])) {
                $msg = 'Handler for formatter type `' . $type . '` not found';
                throw new HandlerNotFoundException($msg);
            }

            $handler = $handlers[$type];
            $index   = $handler['collective'] ? 1 : 0;
            $collectives[$index][] = $field;
        }

        // 2. Collect objects properties which is collective type.
        if ($collectives[1]) {
            $collect_prop = [];
            foreach ($collectives[1] as $field) {
                $type    = $formats[$field]['type'];
                $handler = $handlers[$type];
                $prop    = $handler['field'] ?? $field;
                $collect_prop[$field] = $prop;
            }

            foreach ($objects as $object) {
                foreach ($collect_prop as $field => $prop) {
                    if (isset($object->$prop)) {
                        $collective_data[$field][] = $object->$prop;
                    }
                }

                if (isset($collective_data[$field])) {
                    $unique_vals = array_unique($collective_data[$field]);
                    $collective_data[$field] = $unique_vals;
                }
            }


            // 3. Process collective properties.
            foreach ($collective_data as $field => $values) {
                $type    = $formats[$field]['type'];
                $handler = explode('::', $handlers[$type]['handler']);
                $class   = $handler[0];
                $method  = $handler[1];
                $format  = $formats[$field];
                $fopts   = null;

                if (array_key_exists($field, $options)) {
                    $fopts = $options[$field];
                } elseif (in_array($field, $options)) {
                    $fopts = [];
                }

                $coll_vals = $class::$method($values, $field, $objects, $format, $fopts);
                $collective_data[$field] = $coll_vals;
            }
        }

        // 4. Process non collective, and put collective value
        foreach ($formats as $field => $opts) {
            $type       = $opts['type'];
            $handler    = $handlers[$type];
            $collective = $handler['collective'];

            // for non collective data
            $fopts   = null;
            if (in_array($field, $options)) {
                $fopts = true;
            } elseif (isset($options[$field])) {
                $fopts = $options[$field];
            }

            // for collective data
            $cprop = $handler['field'] ?? $field;
            if (is_string($collective)) {
                $cprop = $collective;
            }

            foreach ($objects as &$object) {
                if (!$collective) {
                    $value = $object->$field ?? null;
                    $res = self::typeApply($type, $value, $field, $object, $opts, $fopts);
                    if (!is_null($res)) {
                        $object->$field = $res;
                    }

                // put collective data
                } else {
                    $value = $object->$cprop ?? null;

                    if (is_object($value)) {
                        $value = (string)$value;
                    }

                    if ($cprop === '_MD5_') {
                        $value = md5($object->$field);
                    }

                    if (isset($collective_data[$field][$value])) {
                        $object->$field = $collective_data[$field][$value];
                    } else {
                        $object->$field = null;
                    }
                }

                if (isset($opts['@rename'])) {
                    $object->{$opts['@rename']} = $object->$field;
                    unset($object->$field);
                }
            }

            unset($object);
        }

        // process askey
        if (!$askey) {
            return $objects;
        }

        return self::propAsKey($objects, $askey);
    }

    public static function formatMany(
        string $format,
        array $objects,
        array $options = [],
        string $askey = null
    ): array {
        $formats = config('formatter.formats.' . $format);

        if (!$formats) {
            $msg = 'Format named `' . $format . '` not exists';
            throw new FormatNotFoundException($msg);
        }

        return self::formatApply($formats, $objects, $options, $askey);
    }

    public static function typeApply(
        string $type,
        $value,
        string $field,
        object $object,
        $format,
        $options
    ) {
        $handlers = config('formatter.handlers');

        if (!isset($handlers[$type])) {
            $msg = 'Handler for formatter type `' . $type . '` not found';
            throw new HandlerNotFoundException($msg);
        }

        $handler = explode('::', $handlers[$type]['handler']);
        $class   = $handler[0];
        $method  = $handler[1];

        return $class::$method($value, $field, $object, $format, $options);
    }
}
