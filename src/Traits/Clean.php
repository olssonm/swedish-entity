<?php

namespace Olssonm\SwedishEntity\Traits;

/**
 * Trait for giving access to the clean()-method
 */
trait Clean
{
    /**
     * Clean the input string
     *
     * @param string $number
     * @return object
     */
    public static function clean(string $number): string
    {
        $pattern = '0123456789-+';
        $number = preg_replace("/[^" . preg_quote($pattern, "/") . "]/", '', $number);

        return $number;
    }
}
