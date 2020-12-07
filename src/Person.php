<?php

namespace Olssonm\SwedishEntity;

use Olssonm\SwedishEntity\Traits\Clean;
use Personnummer\Personnummer;
use Personnummer\PersonnummerException;
use Olssonm\SwedishEntity\Exceptions\PersonException;
use DateTime;

class Person
{
    use Clean;

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
     * @var boolean
     */
    protected $valid = false;

    /**
     * Constructor
     *
     * @param string $ssn
     * @param boolean $allowCoordinationNumbers
     */
    public function __construct(string $ssn, $allowCoordinationNumbers = true)
    {
        $this->ssn = self::clean($ssn);
        $this->valid = $this->valid();

        try {
            $this->personnummer = new Personnummer($ssn, [
                'allowCoordinationNumber' => $allowCoordinationNumbers
            ]);
        } catch (PersonnummerException $e) {
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
     * @param boolean $seperator
     * @return void
     * @throws PersonException
     */
    public function format(int $digits = 10, bool $seperator = false): string
    {
        if (!$this->valid) {
            throw new PersonException();
        }

        $ssn = null;

        if ($digits == 12) {
            $ssn = $this->personnummer->format(true);
        } else {
            $ssn = $this->personnummer->format(false);
        }

        // On "short" variant, the personnummer-instance
        // will always contain a sepeterator
        if (!$seperator && $digits == 10) {
            $ssn = str_replace(['-', '+'], '', $ssn);
        } elseif ($seperator && $digits == 12) {
            if ($this->personnummer->getAge() >= 100) {
                $ssn = substr_replace($ssn, '+', 8, 0);
            } else {
                $ssn = substr_replace($ssn, '-', 8, 0);
            }
        }

        return $ssn;
    }

    /**
     * Checks if the SSN is valid
     *
     * @return boolean
     */
    public function valid(): bool
    {
        $valid = false;

        try {
            $valid = Personnummer::valid($this->ssn);
        } catch (PersonnummerException $e) {
            return false;
        }

        return $valid;
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
        switch ($attr) {
            case 'age':
                return $this->personnummer->getAge();
                break;

            case 'gender':
                return ($this->personnummer->isMale()) ? 'male' : 'female';
                break;

            case 'ssn':
                return $this->ssn;
                break;

            case 'birthday':
                return $this->getBirthday();
                break;

            case 'type':
                return ($this->personnummer->isCoordinationNumber()) ? 'Samordningsnummer' : 'Personnummer';
                break;

            default:
                return $this->personnummer->{$attr};
                break;
        }
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
