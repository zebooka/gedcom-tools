<?php

namespace Zebooka\Gedcom\Model\Date;

class DateApprox
{
    const ABOUT = 'ABT';
    const CALCULATED = 'CAL';
    const ESTIMATED = 'EST';

    /** @var string */
    private $approx;
    /** @var DateExact */
    private $date;

    public function __construct($approx, DateExact $date)
    {
        if (!in_array($approx, [self::ABOUT, self::CALCULATED, self::ESTIMATED])) {
            throw new \UnexpectedValueException("Only ABT/CAL/EST are allowed for approximate date. '{$approx}' was supplied.");
        }
        $this->approx = $approx;
        $this->date = $date;
    }

    public function __toString()
    {
        return "{$this->approx} {$this->date}";
    }
}
