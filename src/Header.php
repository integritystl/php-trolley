<?php 

namespace Integrity\Trolley;

class Header {

    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function key()
    {
        return $this->key;
    }

    public function value()
    {
        return $this->value;
    }

    public function __invoke(): array
    {
        return [$this->key() => $this->value()];
    }
}