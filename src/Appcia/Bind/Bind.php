<?php

namespace Appcia\Bind;

use Appcia\Utils\Arrays;

/**
 * Wrapper for automatic serialization of array
 */
abstract class Bind
{
    /**
     * @var callable
     */
    protected $reader;

    /**
     * @var callable
     */
    protected $writer;

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
     * Bind data / make it ready to be saved in storage (e.g database)
     * Data should be transformed to string (serialized, etc)
     *
     * @return mixed
     */
    abstract protected function encode();

    /**
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
        if (is_array($data)) {
            $data = Arrays::extend($this->getData(), $data);
        } elseif (is_string($data)) {
            $data = Arrays::parse($data);
        }

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
    protected function read($data)
    {
        $callback = $this->reader;
        $callback($data);

        return $this;
    }

    /**
     * Read data from external storage using callback
     *
     * @return mixed
     */
    protected function write()
    {
        $callback = $this->writer;
        $origin = $callback();

        return $origin;
    }
}