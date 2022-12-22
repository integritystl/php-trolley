<?php

namespace Integrity\Trolley\Fields;

abstract class AbstractField implements FieldInterface
{
    use \Integrity\Trolley\Fields\Traits\ValueTrait;

    /**
     * The key/name of the field
     * @var string
     */
    protected $key;

    /**
     * The value of the field
     * @var mixed
     */
    protected $value;

    /**
     * An array of any errors that might have occurred when validating.
     * @var array
     */
    private $errors = [];

    /**
     * By default a field is not "selectable", meaning this entity can
     * not be queried in a "show" controller method by a key value pair
     * representing this field.
     *
     * @var bool
     */
    protected $selectable = false;

    /**
     * @param string $key The field key, for example: 'ID'
     * @param mixed $defaultValue The default value of the field (optional)
     */
    public function __construct(string $key, mixed $defaultValue = null)
    {
        $this->key = $key;
        $this->value = $defaultValue;
    }

    /**
     * Set the key of the field.
     * NOTE: This method must be implimented on any concrete implementation of this abstract class.
     */
    //abstract protected function setKey(): string;

    /**
     * Get the key of the field.
     *
     * @return string The key of the field
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Run validators on the field and return whether or not the field is valid (i.e. no errors).
     *
     * @return bool Whether or not the field is valid, determined by whether or not the `errors` property is empty.
     */
    public function isValid(): bool
    {
        /** Run the validators */
        $this->validate();

        /** If the validators didn't populate the `errors` property, return `true`.  Otherwise, `false`. */
        return empty($this->errors);
    }

    /**
     * Run the validators on the field, and populate the `errors` property array with the messages.
     *
     * @return void
     */
    private function validate(): void
    {
        foreach ($this->getValidators() as $validator) {
            try {
                $this->$validator();
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }

    /**
     * Get the validators for this field, and return their names in an array of strings.
     * This works by getting the name of every method in the field's class, and then filtering out any method that
     * doesn't start with `validates`.
     *
     * @return array An array of validator method names.
     */
    private function getValidators(): array
    {
        /** @var array $validators An array of validator method names. */
        $validators = array_filter(
            get_class_methods($this),
            function ($method) {
                return str_starts_with($method, 'validates');
                ;
            }
        );

        return $validators;
    }

    /**
     * Get the errors that occurred when validating the field.
     *
     * @return array An array of error messages.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check to see if this field is "selectable", meaning this entity can be queried in a "show" controller method by a
     * key value/pair representing this field.  By default, this is `false`, but it can be set to `true` by overriding
     * this function in a concrete implementation.
     *
     * @return bool
     */
    public function isSelectable(): bool
    {
        return $this->selectable;
    }
}
