<?php

namespace Integrity\Trolley\Collections;

use Integrity\Trolley\Collections;
use Integrity\Trolley\Entities\EntityInterface;

/**
 * 
 */
class Collection implements \IteratorAggregate
{
    protected $elements = [];

    /** @var EntityInterface */
    private $entity;

    /**
     * @param array $elements An array
     * @param string $key Optionally provide a key from the array to use as the offset
     */
    public function __construct(array $elements = [], EntityInterface $entity = null, string $key = null)
    {
        if($key) {
            $this->setElementsWithKey($elements, $key);
        } else {
            $this->elements = $elements;
        }

        $this->entity = $entity;
    }

    /**
     * 
     */
    public function get($key)
    {
        if ($this->elements[$key]) {
            $json = json_encode($this->elements[$key]);

            return $this->entity->fromJSON($json);
        }

        return null;
    }

    /**
     * 
     */
    public function set($key, $value)
    {
        $this->elements[$key] = $value;
    }

    private function setElementsWithKey(array $elements = [], string $key = null)
    {
        foreach($elements as $element) {

            // check that child is another array, 
            // and has the specified key
            if(array_key_exists($key, $element)) {
                // if has the specified key, use that as the offset
                $this->set($element[$key], $element);
            } else {
                // if does not have the specified key, give it the next
                // index as its offset
                $this->elements[] = $element;
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function getIterator() {
        return new \ArrayIterator($this);
    }
}
