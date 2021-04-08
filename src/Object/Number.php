<?php

/**
 * Object type number
 * @package iqomp/formatter
 * @version 1.0.0
 */

namespace Iqomp\Formatter\Object;

class Number implements \JsonSerializable
{
    protected $value;
    protected $decimal;
    protected $final;

    public function __construct($value, int $dec = 0)
    {
        $this->value   = $value;
        $this->decimal = $dec;

        if (is_object($value)) {
            $value = (string)$value;
        }

        if (!$dec) {
            $this->final = (int)$value;
        } else {
            $this->final = round(floatval($value), $dec);
        }
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __toString()
    {
        return (string)$this->final;
    }

    public function format(int $dec = 0, string $dsep = ',', string $tsep = '.')
    {
        return number_format($this->final, $dec, $dsep, $tsep);
    }

    public function jsonSerialize()
    {
        return $this->final;
    }
}
