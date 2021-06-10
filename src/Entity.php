<?php

namespace Olssonm\SwedishEntity;

use Olssonm\SwedishEntity\Exceptions\DetectException;
use Olssonm\SwedishEntity\Traits\Clean;

class Entity
{
    use Clean;

    /**
     * Detect if a number is an org-no or ssn
     *
     * @param string $number
     * @return object
     * @throws DetectException
     */
    public static function detect($number): object
    {
        $object = null;

        // Remove seperator
        $number = str_replace('-', '', $number);
        $length = strlen($number);

        if ($length > 10) {
            // More than 10 digits, always a person
            $object = new Person($number);
        } elseif ($length == 10 && substr($number, 2, 2) >= 20) {
            // If the second pair of digits is more or equal to 20, assume organization
            $object = new Organization($number);
        } else {
            // Fallback on person
            $object = new Person($number);
        }

        if (!$object->valid()) {
            throw new DetectException();
        }

        return $object;
    }
}
