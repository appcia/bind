<?php

namespace Appcia\Bind;

/**
 * Wrapper for automatic serialization of array
 */
abstract class Bind
{
    /**
     * @var callable
     */
    protected $writer;

    /**
     * @var callable
     */
    protected $reader;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * Constructor
     *
     * @param callable $reader
     * @param callable $writer
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($reader, $writer)
    {
        if (!is_callable($reader)) {
            throw new \InvalidArgumentException("Bind reader is not callable.");
        }
        $this->reader = $reader;

        if (!is_callable($writer)) {
            throw new \InvalidArgumentException("Bind writer is not callable.");
        }
        $this->writer = $writer;

        $this->decode();
        if ($this->data === null) {
            $this->data = $this->getDefault();
            $this->encode();
        }

        return $this;
    }

    /**
     * Factory method (useful when chained)
     *
     * @param callable $reader
     * @param callable $writer
     *
     * @return static
     */
    public static function factory($reader, $writer)
    {
        return new static($reader, $writer);
    }

    /**
     * Wrap bind, attach reader and writer and push value at once
     * Most commonly used combination
     *
     * @param object $model ORM model with magic getter / setters
     * @param string $prop  ORM property name
     * @param mixed  $data  Data to store
     *
     * @return $this
     */
    public static function wrap($model, $prop, $data = null)
    {
        return static::factory(function () use ($model, $prop) {
            return $model->{$prop};
        }, function ($serial) use ($model, $prop) {
            $model->{$prop} = $serial;
        })->act($data);
    }

    /**
     * Bind data / make it ready to be saved in storage (e.g database)
     * Data should be transformed to string (serialized, etc)
     *
     * @return mixed
     */
    abstract protected function encode();

    /**
     * Unbind data / restore its original format (before binding)
     * Data should be transformed to original format (unserialized, etc)
     *
     * @return mixed
     */
    abstract protected function decode();

    /**
     * Get default value
     *
     * @return mixed
     */
    abstract protected function getDefault();

    /**
     * Get string representation
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->encode();
    }

    /**
     * Act as getter when value is not specified, otherwise as setter
     *
     * @param mixed $value Value
     *
     * @return $this
     */
    public function act($value = null)
    {
        if ($value === null) {
            return $this;
        }

        $this->push($value);

        return $this;
    }

    /**
     * Push mixed value as data
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function push($data)
    {
        $this->setData($data);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->encode();

        return $this;
    }

    /**
     * Write data to external storage using callback
     *
     * @param mixed $data
     *
     * @return $this
     */
    protected function write($data)
    {
        $callback = $this->writer;
        $callback($data);

        return $this;
    }

    /**
     * Read data from external storage using callback
     *
     * @return mixed
     */
    protected function read()
    {
        $callback = $this->reader;
        $origin = $callback();

        return $origin;
    }
}