<?php

namespace Olssonm\SwedishEntity;

use Illuminate\Support\ServiceProvider;
use Olssonm\SwedishEntity\Exceptions\DetectException;

class SwedishEntityServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['validator']->extend('entity', function ($attribute, $value, $parameters): bool {

            $type = isset($parameters[0]) ? $parameters[0] : 'any';

            $object = null;

            if ($type === 'any') {
                try {
                    $object = Entity::detect($value);
                } catch (DetectException $exception) {
                    return false;
                }
            } elseif ($type === 'person') {
                $object = new Person($value);
            } elseif ($type === 'organization') {
                $object = new Organization($value);
            }

            return $object->valid();
        }, 'The :attribute is not a valid entity.');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
