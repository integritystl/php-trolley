<?php

namespace Integrity\Trolley\Fields\Traits;

/**
 * ValueTrait provides a generic getter and setter for a field's value.
 *
 * If you override this in a new field type, consider adding type checking to the setter.
 */
trait ValueTrait
{
    /**
     * Set the value of the field.
     *
     * @param mixed $value The value of the field
     */
    public function set($value): void
    {
        $this->value = $value;
    }

    /**
     * Get the value of the field.
     *
     * @return mixed The value of the field
     */
    public function get()
    {
        return $this->value;
    }
}
