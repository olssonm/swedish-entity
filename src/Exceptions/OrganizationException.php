<?php

namespace Olssonm\SwedishEntity\Exceptions;

use Exception;

class OrganizationException extends Exception
{
    public function __construct(
        $message = 'Invalid swedish organizational number, can not format',
        $code = 400,
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
