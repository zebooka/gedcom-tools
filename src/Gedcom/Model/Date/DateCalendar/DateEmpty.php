<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Zebooka\Gedcom\Model\Date\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateInterface;

class DateEmpty implements DateInterface, DateCalendarInterface
{
    const REGEXP = '/^\s*$/';

    public function __construct()
    {
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode empty date string '{$string}'.");
        }

        return new self();
    }

    public function __toString(): string
    {
        return '';
    }

    public function toTimestamp(): ?int
    {
        return null;
    }

    public function year(): string
    {
        return '';
    }
}
