<?php

namespace Integrity\Trolley\Entities;

use Integrity\Trolley\Fields\FieldInterface;
use Integrity\Trolley\Entities\Exceptions\FieldDoesNotExistException;

/**
 * This is the base class for all entities, whether they be models of database objects or things like
 * Authentication information.
 *
 * `BaseEntity` extends this and adds methods for querying the database and turning `Response` objects into "things".
 *
 * `Authentication` also extends this.
 *
 * This class also includes our seralization methods which convert entities to and from JSON.
 */
abstract class AbstractEntity implements EntityInterface
{

    const FIELD_NAMESPACE = '\Integrity\Trolley\Fields';
    /**
     * An array of dependencies.  Populating this in the constructor triggers the boot method.
     * @var array
     */
    protected $di;

    /**
     * User defined fields
     */
    protected $fields = [];

    /**
     * array of entity field instances
     */
    private $entityFields = [];

    /**
     * Errors that might have occurred when validating.
     * @var array
     */
    private $errors = [];

    /**
     * A new instance of an entity will optionally take a set of fields to overwrite the default and will trigger the
     * boot method to crete the instances of the fields on the entity object.
     *
     * @var array $di An optional array of fields to overwrite the default set of fields
     */
    public function __construct(array $di = [])
    {
        if (!empty($di)) {
            $this->di = $di;
        }
        $this->boot();
    }

    /**
     * Create the dependent field objects and adds them to the entity's fields array..
     *
     * If there are any dependencies in the $di array, this method will iterate through each one, instantiate it, and
     * add it.
     *
     * @return void
     */
    protected function boot(): void
    {
        if (!empty($this->fields)) {

            foreach ($this->fields as $field) {

                // divide config up..
                $fieldConfig = $this->explodeField($field);

                $fieldClass = self::FIELD_NAMESPACE . '\\' . ucfirst($fieldConfig['type']) . 'Field';

                // look in Fields for a field
                if (class_exists($fieldClass)) {

                    /** @var FieldInterface $field An instance of the class found iterating through the dependencies */
                    $fieldInstance = new $fieldClass($fieldConfig['key']);

                    /** Add the field to the entity's fields array. */
                    $this->addField($fieldInstance);
                } else {
                    echo "\n class does not exist.\n";
                }
            }
        }
    }

    /**
     * Make this a helper??
     */
    private function explodeField(string $field)
    {
        // divide config up..
        $fieldConfig = explode(':', $field);

        return [
            'key' => $fieldConfig[0],
            'type' => $fieldConfig[1],
        ];
    }

    /**
     * Add a field to the entity's fields array.
     *
     * @param FieldInterface $field The field to add to the entity
     * @param string $key (Optional) The key to use for the field, defaults to the field's usual key
     *
     * @return void
     */
    protected function addField(FieldInterface $field, string $key = null): void
    {
        /** if a key override is provided, use that. Otherwise, use the key defined on the field object. */
        $key = isset($key) ? $key : $field->key();

        /** Add the field to this entity's fields array. */
        $this->entityFields[$key] = $field;
    }

    /**
     * Lets you provide a callback to filter the fields, and ignores fields without values set.
     *
     * @param callable $callback A callback function to filter by
     * @return array An array of fields that pass the filter.
     */
    private function filterFields(callable $callback): array
    {
        return array_values(
            /**  filter out null */
            array_filter(
                /** map using provided callback */
                array_map($callback, $this->entityFields),
                function ($value) {
                    return !is_null($value);
                }
            )
        );
    }

    /**
     * Get the field names of the entity.
     *
     * @return array An array of field names
     */
    public function getFieldNames(): array
    {
        return $this->filterFields(
            function ($field) {
                if ($field->get()) {
                    return $field->key();
                }
            },
        );
    }

    /**
     * Get the field values
     *
     * @return array An array of field values
     */
    public function getFieldData(): array
    {
        return $this->filterFields(
            function ($field) {
                if ($field->get()) {
                    return $field->get();
                }
            },
        );
    }

    /**
     * Checks if the entity has the field specified by key
     *
     * @param string $key The key of the field to check for
     * @return bool True if the field exists, false otherwise
     */
    public function hasField(string $key): bool
    {
        if (!array_key_exists($key, $this->entityFields)) {
            return false;
        }

        return true;
    }

    /**
     * Gets a field object by key
     *
     * @param string $key The key of the field to get
     * @throws FieldDoesNotExistException when the field does not exist
     * @return FieldInterface|bool The field object or false when field does not exist
     */
    public function getField(string $key): FieldInterface|bool
    {
        if (!$this->hasField($key)) {
            throw new FieldDoesNotExistException();
        }

        return $this->entityFields[$key];
    }

    /**
     * Sets the value in the field
     *
     * @param string $key The key of the field we want to set the value of
     * @param mixed $value The value we want to set the field to
     * @throws FieldDoesNotExistException When the field does not exist
     * @return void
     */
    public function __set($key, $value): void
    {
        if (!$this->hasField($key)) {
            throw new FieldDoesNotExistException();
        }

        $this->entityFields[$key]->set($value);
    }

    /**
     * Gets the value from the field
     *
     * @param string $key The key of the field we want to get the value of
     * @throws FieldDoesNotExistException when the field does not exist
     * @return mixed The value of the field
     */
    public function __get($key): mixed
    {
        if (!$this->hasField($key)) {
            throw new FieldDoesNotExistException();
        }

        return $this->entityFields[$key]->get();
    }

    /**
     * Validates a field by it's own `isValid()` method's rules, and populates the errors array with any errors that
     * occur.  If any errors DO occur, this method returns false.  If no errors occur, this method returns true.
     *
     * @return bool True if no errors occur, false otherwise
     */
    public function validate(): bool
    {
        foreach ($this->entityFields as $field) {
            if (!$field->isValid()) {
                $this->errors[$field->key()] = $field->getErrors();
            }
        }

        return empty($this->errors);
    }

    /**
     * Get the errors that occurred during validation
     *
     * @return array An array of errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns itself as a JSON string.
     *
     * @return string JSON string of this object
     */
    public function toJSON(): string
    {
        /** @var array $export An array of this entity's fields to be JSONified */
        $export = [];

        foreach ($this->entityFields as $field) {
            /** @var mixed $value The value of the field we want to populate the array with.  Used to check for null. */
            $value = $field->get();

            if (!is_null($value)) {
                $export[$field->key()] = $field->get();
            }
        }

        return json_encode($export);
    }

    /**
     * Takes a JSONified entity and returns an instance of the entity populated by the JSON data.
     * 
     * override this method  in a concrete entity object if its json  has a differnt structure
     *
     * @param string $json The JSON string to populate the entity with
     * @return EntityInterface An instance of the entity populated by the JSON data
     */
    public static function fromJSON(string $json): EntityInterface
    {
        /** Turn the JSON string into something we can iterate through to populate the new entity. */
        /** @var mixed $import The JSON to be decoded to an iterable. */
        $import = json_decode($json, true);

        /** Instantiate a new Whatever This Entity Is.  We're going to populate it with the JSON key->value pairs.  */
        /** @var EntityInterface $self An instance of the entity to be populated by the JSON data. */
        $self = new static();

        /** Iterate through the JSON data and populate the entity with it. */
        foreach ($import as $key => $value) {
            if (!is_null($value)) {
                $self->$key = $value;
            }
        }

        /** Return the re-hydrated entity. */
        return $self;
    }
}
