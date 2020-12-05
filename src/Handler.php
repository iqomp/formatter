<?php

/**
 * Format default handler
 * @package iqomp/formatter
 * @version 1.0.0
 */

namespace Iqomp\Formatter;

use Iqomp\Config\Fetcher as Config;
use Iqomp\Formatter\HandlerNotFoundException;
use Iqomp\Formatter\Object\{
    DateTime,
    Embed,
    Interval,
    Location,
    Number,
    Std,
    Text
};

class Handler
{
    protected static function getPropValue(object $object, string $field)
    {
        $obj  = clone $object;
        $keys = explode('.', $field);

        foreach ($keys as $ky) {
            if (is_array($obj)) {
                $obj = $obj[$ky];
            } elseif (is_object($obj)) {
                $obj = $obj->$ky;
            }

            if (!is_array($obj) && !is_object($obj)) {
                return $obj;
            }
        }

        return $obj;
    }

    public static function boolean($value)
    {
        return (bool)$value;
    }

    public static function clone($val, string $fld, object $obj, array $fmt, $opts)
    {
        if (isset($fmt['source'])) {
            $source = $fmt['source'];
            $res    = self::getPropValue($obj, $source['field']);

            if (!isset($source['type'])) {
                return $res;
            }

            $type = $source['type'];
            return Formatter::typeApply($type, $res, $fld, $obj, $fmt, $opts);
        }

        if (!isset($fmt['sources'])) {
            return null;
        }

        $result = (object)[];
        foreach ($fmt['sources'] as $prop => $opt) {
            $val = self::getPropValue($obj, $opt['field']);

            if (isset($opt['type'])) {
                $type = $opt['type'];
                $val = Formatter::typeApply($type, $val, $fld, $obj, $fmt, $opts);
            }

            $result->$prop = $val;
        }

        return $result;
    }

    public static function custom($val, string $fld, object &$obj, array $fmt, $opts)
    {
        $handler = explode('::', $fmt['handler']);
        $class   = $handler[0];
        $method  = $handler[1];

        return $class::$method($val, $fld, $obj, $fmt, $opts);
    }

    public static function date($val, string $fld, object $obj, array $fmt)
    {
        if (isset($fmt['timezone'])) {
            $val = new DateTime($val, new \DateTimeZone($fmt['timezone']));
        } else {
            $val = new DateTime($val);
        }

        return $val;
    }

    public static function delete($val, string $field, object &$object)
    {
        if (property_exists($object, $field)) {
            unset($object->$field);
        }
    }

    public static function embed($value)
    {
        return new Embed($value);
    }

    public static function interval($value)
    {
        return new Interval($value);
    }

    public static function multipleText($val, string $fld, object $obj, array $fmt)
    {
        $sep = $fmt['separator'] ?? PHP_EOL;

        if ($sep === 'json') {
            $vals = json_decode($val);
        } else {
            $vals   = explode($sep, $val);
        }

        $result = [];

        foreach ($vals as $val) {
            $result[] = new Text(trim($val));
        }

        return $result;
    }

    public static function number($value, string $fld, object $obj, array $fmt)
    {
        $dec = $fmt->decimal ?? 0;
        return new Number($value, $dec);
    }

    public static function text($value)
    {
        return new Text($value);
    }

    public static function json($value, string $fld, object $obj, array $fmt, $opts)
    {
        $value = json_decode($value);

        if (!isset($fmt['format'])) {
            return $value;
        }

        if (!is_array($opts)) {
            $opts = [];
        }

        if (is_array($value)) {
            return Formatter::formatMany($fmt['format'], $value, $opts);
        }

        if (is_object($value)) {
            return Formatter::format($fmt['format'], $value, $opts);
        }

        return $value;
    }

    public static function join($val, string $fld, object $obj, array $fmt)
    {
        $fields    = $fmt['fields'];
        $separator = $fmt['separator'] ?? '';
        $result    = [];

        foreach ($fields as $fld) {
            if (substr($fld, 0, 1) === '$') {
                $result[] = (string)self::getPropValue($obj, substr($fld, 1));
            } else {
                $result[] = $fld;
            }
        }

        return implode($separator, $result);
    }

    public static function rename($val, string $fld, object &$obj, array $fmt)
    {
        $to = $fmt['to'];
        $obj->$to = $val;
        unset($obj->$fld);
    }

    public static function stdId($val)
    {
        return new Std($val);
    }

    public static function switch($val, string $fld, object $obj, array $fmt, $opts)
    {
        $cases = $fmt['case'];

        $result = null;

        foreach ($cases as $case) {
            $other_field = $case['field'];
            $other_val   = null;

            if (substr($other_field, 0, 1) === '$') {
                $other_val = self::getPropValue($obj, substr($other_field, 1));
            } else {
                $other_val = $obj->{$other_field} ?? null;
            }

            $expect_val = $case['expected'];
            $operator   = $case['operator'];

            $match = false;

            switch ($operator) {
                case '=':
                    $match = $other_val == $expect_val;
                    break;
                case '!=':
                    $match = $other_val != $expect_val;
                    break;
                case '>':
                    $match = $other_val > $expect_val;
                    break;
                case '<':
                    $match = $other_val < $expect_val;
                    break;
                case '>=':
                    $match = $other_val >= $expect_val;
                    break;
                case '<=':
                    $match = $other_val <= $expect_val;
                    break;
                case 'in':
                    $match = in_array($other_val, $expect_val);
                    break;
                case '!in':
                    $match = !in_array($other_val, $expect_val);
                    break;
            }

            if (!$match) {
                continue;
            }

            $result = $case['result'];
            break;
        }

        if (!$result) {
            return $val;
        }

        $handler = Config::get('formatter', 'handlers', $result['type']);

        if (!$handler) {
            $msg = 'Handler for formatter type `' . $type . '` not found';
            throw new HandlerNotFoundException($msg);
        }

        // for non collective
        if (!$handler['collective']) {
            return Formatter::typeApply($result['type'], $val, $fld, $obj, $result, $opts);
        }

        $handler = explode('::', $handler['handler']);

        // for collective
        $class  = $handler[0];
        $method = $handler[1];
        $format = $result;

        $values = $class::$method([$val], $fld, [$obj], $format, $opts);
        return $values[$val] ?? $val;
    }
}
