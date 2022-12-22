<?php

namespace Integrity\Trolley\Entities;

interface EntityInterface
{
    public function __set($key, $value): void;
    public function __get($key);
    public static function fromJSON(string $json): EntityInterface;
}
