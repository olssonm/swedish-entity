<?php

namespace Olssonm\SwedishEntity;

use DateTime;
use Personnummer\Personnummer;
use Personnummer\PersonnummerException;
use Olssonm\SwedishEntity\Exceptions\PersonException;

class Person
{
    /**
     * The set SSN
     *
     * @var string
     */
    private $ssn;

    /**
     * The Personnummer-instance
     *
     * @var Personnummer
     */
    private $personnummer;

    /**
     * If object is valid
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * The person parts
     *
     * @var array
     */
    protected $parts = [];

    /**
     * Constructor
     *
     * @param string $ssn
     * @param bool $allowCoordinationNumbers
     */
    public function __construct(string $ssn, $allowCoordinationNumbers = true)
    {
        $this->ssn = $ssn;
        $this->valid = $this->valid();

        try {
            $this->personnummer = new Personnummer($ssn, [
                'allowCoordinationNumber' => $allowCoordinationNumbers
            ]);
            $this->setParts();
        } catch (PersonnummerException $exception) {
            //
        }
    }

    /**
     * Returns the Personnummer-instance
     *
     * @return Personnummer
     */
    public function getPersonnummerInstance(): Personnummer
    {
        return $this->personnummer;
    }

    /**
     * Format the SSN
     *
     * @param int $digits
     * @param bool $seperator
     * @return string
     * @throws PersonException
     */
    public function format(int $digits = 10, bool $seperator = true): string
    {
        if (!$this->valid) {
            throw new PersonException();
        }

        $ssn = $this->personnummer->format($digits == 12);

        // On "short" variant, the personnummer-instance
        // will always contain a sepeterator
        if (!$seperator && $digits == 10) {
            $ssn = str_replace(['-', '+'], '', $ssn);
        } elseif ($seperator && $digits == 12) {
            $ssn = substr_replace($ssn, '-', 8, 0);

            // If older than 100, we need a '+' seperator
            if ($this->personnummer->getAge() >= 100) {
                $ssn = str_replace('-', '+', $ssn);
            }
        }

        return $ssn;
    }

    /**
     * Checks if the SSN is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return Personnummer::valid($this->ssn);
    }

    /**
     * Set the parts of the persons SSN
     *
     * @return void
     */
    public function setParts(): void
    {
        $this->parts = [
            'age' => $this->personnummer->getAge(),
            'gender' => ($this->personnummer->isMale()) ? 'male' : 'female',
            'ssn' => $this->ssn,
            'birthday' => $this->getBirthday(),
            'type' => ($this->personnummer->isCoordinationNumber()) ?
                'Samordningsnummer' :
                'Personnummer'
        ];
    }

    /**
     * Dynamic getter; retrieves attribute from the
     * Personnummer-object with some additionals
     *
     * @param string $attr
     * @return mixed
     */
    public function __get(string $attr)
    {
        if (isset($this->parts[$attr])) {
            return $this->parts[$attr];
        }

        return $this->personnummer->{$attr};
    }

    /**
     * Get the person's birthday
     *
     * @return DateTime
     */
    private function getBirthday(): DateTime
    {
        $day = intval($this->personnummer->day);
        if ($this->personnummer->isCoordinationNumber()) {
            $day -= 60;
        }

        return new DateTime(
            sprintf(
                '%s%s-%s-%d',
                $this->personnummer->century,
                $this->personnummer->year,
                $this->personnummer->month,
                $day
            )
        );
    }
}
