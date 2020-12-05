<?php

/**
 * Object type std
 * @package iqomp/formatter
 * @version 1.0.0
 */

namespace Iqomp\Formatter\Object;

class Std implements \JsonSerializable
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return (string)$this->id;
    }

    public function jsonSerialize()
    {
        return (object)['id' => $this->id];
    }
}
