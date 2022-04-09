<?php

namespace Zebooka\Gedcom\Model\Date;

use Zebooka\Gedcom\Model\Date\DateCalendar\DateEmpty;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateGregorian;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateHebrew;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateJulian;

class DateCalendar
{
    private static $monthsList = [
        ['VEND', 'BRUM', 'FRIM', 'NIVO', 'PLUV', 'VENT', 'GERM', 'FLOR', 'PRAI', 'MESS', 'THER', 'FRUC', 'COMP'],
    ];

    public static function fromString(string $string): DateCalendarInterface
    {
        if (preg_match(DateGregorian::REGEXP, $string)) {
            return DateGregorian::fromString($string);
        } elseif (preg_match(DateJulian::REGEXP, $string)) {
            return DateJulian::fromString($string);
        } elseif (preg_match(DateHebrew::REGEXP, $string)) {
            return DateHebrew::fromString($string);
        } elseif (preg_match(DateEmpty::REGEXP, $string)) {
            return DateEmpty::fromString($string);
        } else {
            throw new \UnexpectedValueException("Unable to decode calendar date string '{$string}'.");
        }
    }
}
