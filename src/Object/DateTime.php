<?php

/**
 * Object type datetime
 * @package iqomp/formatter
 * @version 1.0.0
 */

namespace Iqomp\Formatter\Object;

class DateTime extends \DateTime implements \JsonSerializable
{
    private $timezone;
    private $time;
    private $value;

    public function __construct(string $time = null, \DateTimeZone $tz = null)
    {
        if (is_null($time)) {
            return;
        }

        parent::__construct($time, $tz);

        $this->value    = $time;
        $this->time     = $this->getTimestamp();
        $this->timezone = $this->getTimezone()->getName();
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __toString()
    {
        return $this->time ? $this->format('c') : '';
    }

    public function jsonSerialize()
    {
        return $this->time ? $this->__toString() : null;
    }
}
