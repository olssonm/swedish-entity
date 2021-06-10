<?php

namespace Olssonm\SwedishEntity;

use Olssonm\SwedishEntity\Exceptions\OrganizationException;

class Organization
{
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
    protected $parts = [];

    /**
     * If object is valid
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * Constructor
     *
     * @param string $orgNo
     */
    public function __construct(string $orgNo)
    {
        $this->orgNo = $orgNo;
        $this->valid = $this->valid();

        if ($this->valid) {
            $this->setParts();
        }
    }

    /**
     * Format the organizational number
     *
     * @param bool $seperator
     * @return string
     * @throws OrganizationException
     */
    public function format(bool $seperator = true): string
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
     * @return bool
     */
    public function valid(): bool
    {
        if (!preg_match("/^\d{6}[\-\/]?\d{4}+$/", $this->orgNo)) {
            return false;
        }

        // Second "pair" (22 in 112233-4455) is always higher than 20
        if (substr($this->orgNo, 2, 2) < 20) {
            return false;
        }

        $orgNo = str_replace(['-'], '', $this->orgNo);
        $orgNo = array_reverse(str_split($orgNo));

        // Luhn
        $sum = 0;
        foreach ($orgNo as $key => $number) {
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
     * @return string|\ErrorException
     */
    public function __get(string $attr)
    {
        if (isset($this->parts[$attr])) {
            return $this->parts[$attr];
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        return trigger_error(
            sprintf(
                'Undefined property via __get(): %s in %s on line %d',
                $attr,
                $trace[0]['file'],
                $trace[0]['line']
            ),
            E_USER_NOTICE
        );
    }

    /**
     * Parse the type
     *
     * @param int $type
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
