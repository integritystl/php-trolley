<?php

namespace Integrity\Trolley\Entities\Exceptions;

class FieldDoesNotExistException extends \Exception
{
    protected $message = 'Field does not exist';
}