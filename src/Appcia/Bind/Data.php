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
     * @return $this
     * @throws \UnexpectedValueException
     */
    protected function check()
    {
        if (!is_array($this->data)) {
            throw new \UnexpectedValueException(sprintf("Bind data is not an array: '%s'", gettype($this->data)));
        }

        return $this;
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
}