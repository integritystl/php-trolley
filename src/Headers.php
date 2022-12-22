<?php

namespace Integrity\Trolley;

use Integrity\Trolley\Header;

class Headers
{
    private $headers = [];

    public function add($key, $value)
    {
        $this->headers[] = new Header($key, $value);
    }

    public function toArray()
    {
        $headers = [];

        foreach ($this->headers as $header) {
            $headers[$header->key()] = $header->value();
        }

        return $headers;
    }

    public function __invoke()
    {
        return $this->toArray();
    }
}