<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Model\Date\DateApproximate;
use Zebooka\Gedcom\Model\Date\DateCalendar;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateEmpty;
use Zebooka\Gedcom\Model\Date\DateInt;
use Zebooka\Gedcom\Model\Date\DatePeriod;
use Zebooka\Gedcom\Model\Date\DatePhrase;
use Zebooka\Gedcom\Model\Date\DateRange;

class DateFactory
{
    public static function fromString($string): DateInterface
    {
        if (preg_match(DateEmpty::REGEXP, $string)) {
            return DateEmpty::fromString($string);
        } elseif (preg_match(DatePeriod::REGEXP, $string)) {
            return DatePeriod::fromString($string);
        } elseif (preg_match(DateRange::REGEXP, $string)) {
            return DateRange::fromString($string);
        } elseif (preg_match(DateApproximate::REGEXP, $string)) {
            return DateApproximate::fromString($string);
        } elseif (preg_match(DateInt::REGEXP, $string)) {
            return DateInt::fromString($string);
        } elseif (preg_match(DatePhrase::REGEXP, $string)) {
            return DatePhrase::fromString($string);
        } else {
            return DateCalendar::fromString($string);
        }
    }
}
