<?php

namespace Zebooka\Gedcom\Model\Date;

use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;

class DateApproximate implements DateInterface, YearInterface
{
    const ABOUT = 'ABT';
    const CALCULATED = 'CAL';
    const ESTIMATED = 'EST';

    const REGEXP = '/^(?<approx>(?:ABT|CAL|EST))\s+(?<date>.+)$/';

    /** @var string */
    private $approx;
    /** @var DateCalendar */
    private $date;

    public function __construct($approx, DateCalendarInterface $date)
    {
        if (!in_array($approx, [self::ABOUT, self::CALCULATED, self::ESTIMATED])) {
            throw new \UnexpectedValueException("Only ABT/CAL/EST are allowed for approximate date. '{$approx}' was supplied.");
        }
        $this->approx = $approx;
        $this->date = $date;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode approx date string '{$string}'.");
        }

        return new self($m['approx'], DateCalendar::fromString($m['date']));
    }

    public function __toString(): string
    {
        return "{$this->approx} {$this->date}";
    }

    public function year(): ?int
    {
        return ($this->date instanceof YearInterface ? $this->date->year() : null);
    }
}
