<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Model\Date\DateApproximate;
use Zebooka\Gedcom\Model\Date\DateExact;
use Zebooka\Gedcom\Model\Date\DatePeriod;
use Zebooka\Gedcom\Model\Date\DateRange;

class DateFactory
{
    public static function fromString($string): DateInterface
    {
        if (preg_match(DatePeriod::REGEXP, $string)) {
            return DatePeriod::fromString($string);
        } elseif (preg_match(DateRange::REGEXP, $string)) {
            return DateRange::fromString($string);
        } elseif (preg_match(DateApproximate::REGEXP, $string)) {
            return DateApproximate::fromString($string);
        } else {
            return DateExact::fromString($string);
        }
    }
}
