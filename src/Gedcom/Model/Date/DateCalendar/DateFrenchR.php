<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Zebooka\Gedcom\Model\Date\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateInterface;

class DateFrenchR implements DateInterface, DateCalendarInterface
{
    const REGEXP = '/^(?:(?<calendar>FRENCH_R|@#DFRENCH R@)\s+)/';

    public function __construct()
    {
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode French Revolution calendar date string '{$string}'.");
        }
        throw new \RuntimeException('French Revolution calendar date not implemented yet.');
    }

    public function __toString(): string
    {
        throw new \RuntimeException('French Revolution calendar date not implemented yet.');
    }

    public function toTimestamp(): ?int
    {
        throw new \RuntimeException('Not implemented yet.');
    }
}
