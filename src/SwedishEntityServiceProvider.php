<?php

namespace Olssonm\SwedishEntity;

use Illuminate\Support\ServiceProvider;

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

            try {
                if ($type === 'any') {
                    $object = Entity::detect($value);
                } elseif ($type === 'person') {
                    $object = new Person($value);
                } elseif ($type === 'person_only_coordination') { // only ssn
                    $object = new Person($value);
                    if ($object->type == Person::PERSONNUMMER) {
                        return false;
                    }
                } elseif ($type === 'person_only_ssn') { // only coordination number
                    $object = new Person($value);
                    if ($object->type == Person::SAMORDNINGSNUMMER) {
                        return false;
                    }
                } elseif ($type === 'organization') {
                    $object = new Organization($value);
                }
            } catch (\Throwable $exception) {
                return false;
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
