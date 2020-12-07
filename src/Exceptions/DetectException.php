<?php

namespace Olssonm\SwedishEntity\Exceptions;

use Exception;

class DetectException extends Exception
{
    public function __construct(
        $message = 'Could not detect or parse the input',
        $code = 400,
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
