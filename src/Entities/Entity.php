<?php

namespace Integrity\Trolley\Entities;

use Integrity\Trolley\Collections\Collection;
use Integrity\Trolley\Entities\Exceptions\FieldIsNotSelectableException;
use Integrity\Trolley\Fields\CustomField;
use Integrity\Trolley\Helpers\Query;
use Integrity\Trolley\Http\Responses\Response;

/**
 * Used for modeling datataypes from requests, this is the base class of entities that are used to represent
 * data.
 */
abstract class Entity extends AbstractEntity
{

}