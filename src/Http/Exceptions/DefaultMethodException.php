<?php

namespace Integrity\Trolley\Http\Exceptions;

class DefaultMethodException extends \Exception
{
    protected $message = 'Method not available for this endpoint.';
}