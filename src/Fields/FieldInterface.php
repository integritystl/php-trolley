<?php

namespace Integrity\Trolley\Fields;

interface FieldInterface
{
    public function __construct(string $key, $defaultValue = null);
    public function key(): string;
    public function set($value): void;
    public function get();
}
