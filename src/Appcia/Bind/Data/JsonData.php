<?php

namespace Appcia\Utils\Bind\Data;

use Appcia\Bind\Data;
use Appcia\Utils\Json;

/**
 * Data bind encoded in JSON format
 */
class JsonData extends Data
{
    /**
     * {@inheritdoc}
     */
    protected function encode()
    {
        $json = $this->data;
        if (!Json::isEncoded($json)) {
            $json = Json::encode($json);
        }

        $this->read($json);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function decode()
    {
        $data = $this->write();
        if (Json::isEncoded($data)) {
            $data = Json::decode($data);
        }

        $this->data = $data;

        return $this;
    }
}