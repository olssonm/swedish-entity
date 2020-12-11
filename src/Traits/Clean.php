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
     * @return string
     */
    public static function clean(string $number): string
    {
        $pattern = '0123456789-+';
        return preg_replace("/[^" . preg_quote($pattern, "/") . "]/", '', $number);
    }
}
