<?php

namespace Olssonm\SwedishEntity\Traits;

trait Clean
{
    /**
     * Clean/remove illegal characters from string
     *
     * @param string $string
     * @return string
     */
    public static function clean(string $string): string
    {
        $pattern = '0123456789-+';
        return preg_replace("/[^" . preg_quote($pattern, "/") . "]/", '', $string);
    }
}
