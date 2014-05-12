<?php

namespace Appcia\Bind\Data;

use Appcia\Bind\Data;
use Appcia\Utils\Php;

/**
 * Data bind encoded in PHP serialization format
 */
class PhpData extends Data
{
    /**
     * {@inheritdoc}
     */
    protected function encode()
    {
        $serial = $this->data;
        if (!Php::isEncoded($serial)) {
            $serial = Php::encode($this->data);
        }

        $this->read($serial);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function decode()
    {
        $data = $this->write();
        if (Php::encode($data)) {
            $data = Php::decode($data);
        }
        $this->data = $data;

        return $this;
    }
}