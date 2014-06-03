<?php

namespace Appcia\Bind;

use Appcia\Utils\Arrays;

/**
 * Data bind
 */
abstract class Data extends Bind implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * {@inheritdoc}
     */
    protected function getDefault()
    {
        return array();
    }

    /**
     * Set value by key
     *
     * @param int|string $key
     * @param mixed      $value
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function set($key, $value = null)
    {
        $this->check();

        if (!Arrays::isArray($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            Arrays::setPath($this->data, $k, $v);
        }

        $this->encode();

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
     * Remove value by key
     *
     * @param int|string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        $this->check();

        Arrays::clear($this->data, $key);
        $this->encode();

        return $this;
    }

    /**
     * Check whether value exists by key
     *
     * @param int|string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        $this->check();

        return array_key_exists($key, $this->data);
    }

    /**
     * Get value by key
     *
     * @param int|string $key
     * @param mixed      $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $this->check();
        $value = Arrays::getPath($this->data, $key, $default);

        return $value;
    }

    /**
     * Check whether bind data is array (key / value storage)
     *
     * @param bool $verbose
     *
     * @throws \UnexpectedValueException
     * @return $this
     */
    protected function check($verbose = true)
    {
        $valid = is_array($this->data);
        if (!$valid && $verbose) {
            throw new \UnexpectedValueException(sprintf("Bind data is not an array: '%s'", gettype($this->data)));
        }

        return $valid;
    }

    /**
     * Allows iterating if data is in array
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->check();

        return new \ArrayIterator($this->data);
    }

    /**
     * Allows use count() on binding if data is an array
     *
     * {@inheritdoc}
     */
    public function count()
    {
        $this->check();

        return count($this->data);
    }

    /**
     * Set value as string representation from an array
     *
     * @param mixed $key
     * @param array $value
     *
     * @return $this
     */
    public function compose($key, $value)
    {
        return $this->set($key, Arrays::compose($value));
    }

    /**
     * Get value as an array from string representation
     *
     * @param mixed $key
     *
     * @return array
     */
    public function parse($key)
    {
        return Arrays::parse($this->get($key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->has($offset)
            ? $this->get($offset)
            : null;
    }

    /**
     * @see get()
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    /**
     * @see set()
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ($this->check(false)) {
            return Arrays::compose($this->data);
        } else {
            return parent::__toString();
        }
    }
}
