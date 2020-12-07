<?php

namespace Olssonm\SwedishEntity\Exceptions;

use Exception;

class PersonException extends Exception
{
    public function __construct(
        $message = 'Invalid swedish social security number, can not format',
        $code = 400,
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
