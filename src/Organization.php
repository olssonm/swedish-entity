<?php

namespace Olssonm\SwedishEntity;

use Olssonm\SwedishEntity\Exceptions\OrganizationException;
use Olssonm\SwedishEntity\Traits\Clean;

class Organization
{
    use Clean;

    /**
     * The organizational number
     *
     * @var string
     */
    protected $orgNo;

    /**
     * The organizational numbers parts
     *
     * @var array
     */
    protected $parts = [
        'type' => null,
        'org_no' => null,
        'check' => null
    ];

    /**
     * If object is valid
     *
     * @var boolean
     */
    protected $valid = false;

    /**
     * Constructor
     *
     * @param string $orgNo
     */
    public function __construct(string $orgNo)
    {
        $this->orgNo = self::clean($orgNo);
        $this->valid = $this->valid();

        if ($this->valid) {
            $this->setParts();
        }
    }

    /**
     * Format the ORGNO
     *
     * @param boolean $seperator
     * @return string
     * @throws OrganizationException
     */
    public function format(bool $seperator = true)
    {
        if (!$this->valid) {
            throw new OrganizationException();
        }

        $orgNo = str_replace('-', '', $this->orgNo);

        if (!$seperator) {
            return $orgNo;
        }

        return substr_replace($orgNo, '-', 6, 0);
    }

    /**
     * Check if the ORGNO is valid
     *
     * @return boolean
     */
    public function valid(): bool
    {
        if (substr($this->orgNo, 2, 2) < 20) {
            return false;
        }

        if (strlen($this->orgNo) > 11) {
            return false;
        }

        $orgNo = str_replace(['-'], '', $this->orgNo);

        $orgNo = array_reverse(str_split($orgNo));

        // Luhn
        $sum = 0;
        foreach ($orgNo as $key => $number) {
            if (!is_numeric($number)) {
                return false;
            }
            if ($key % 2) {
                $number = $number * 2;
            }
            $sum += ($number >= 10 ? $number - 9 : $number);
        }
        return ($sum % 10 === 0);
    }

    /**
     * Set the parts of the organizational number
     *
     * @return void
     */
    protected function setParts(): void
    {
        $this->parts['check'] = substr($this->orgNo, strlen($this->orgNo) - 1, 1);
        $this->parts['org_no'] = $this->orgNo;
        $this->parts['type'] = $this->parseType((int)substr($this->orgNo, 0, 1));
    }

    /**
     * Dynamic getter
     *
     * @param string $attr
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->parts[$name])) {
            return $this->parts[$name];
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        trigger_error(
            sprintf(
                'Undefined property via __get(): %s in %s on line %d',
                $name,
                $trace[0]['file'],
                $trace[0]['line']
            ),
            E_USER_NOTICE
        );

        return null;
    }

    /**
     * Parse the type
     *
     * @param integer $type
     * @return string
     */
    protected function parseType(int $type): string
    {
        return [
            1 => 'Dödsbon',
            2 => 'Stat, landsting och kommuner',
            5 => 'Aktiebolag',
            6 => 'Enkelt bolag',
            7 => 'Ekonomiska föreningar',
            8 => 'Ideella föreningar och stiftelser',
            9 => 'Handelsbolag, kommanditbolag och enkla bolag',
        ][$type];
    }
}
