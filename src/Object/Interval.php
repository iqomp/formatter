<?php

/**
 * Object type interval
 * @package iqomp/formatter
 * @version 1.0.0
 */

namespace Iqomp\Formatter\Object;

class Interval implements \JsonSerializable
{
    protected $time;
    protected $value;

    protected $DateInterval;
    protected $DateTime;

    public function __construct(string $diff = null)
    {
        if (is_null($diff)) {
            return;
        }

        $this->value = $diff;

        $this->DateInterval = new \DateInterval($diff);

        $this->DateTime = new \DateTime();
        $this->DateTime->add($this->DateInterval);

        $this->time = $this->DateTime->getTimestamp() - time();
    }

    public function __toString()
    {
        return $this->interval();
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function interval(): string
    {
        $props = [
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        ];

        $format    = [];
        $last_child = null;

        foreach ($props as $prop => $label) {
            $c_val = $this->DateInterval->$prop;
            if (!$c_val) {
                continue;
            }
            $last_child = '%' . $prop . ' ' . $label . ($c_val > 1 ? 's' : '');
            $format[] = $last_child;
        }

        if (count($format) > 1) {
            $format[count($format) - 1] = 'and ' . $last_child;
        }

        return $this->DateInterval->format(implode(' ', $format));
    }

    public function format(string $format = null): string
    {
        return $this->DateTime->format($format);
    }

    public function jsonSerialize()
    {
        if (!$this->value) {
            return null;
        }

        return (object)[
            'time'      => $this->time,
            'date'      => $this->format('c'),
            'interval'  => $this->interval()
        ];
    }
}
