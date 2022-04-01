<?php

namespace Zebooka\Gedcom\Model;

class Date
{
    const DATE_REGEXP = '/^(?:(?<lax>FROM|TO|ABT|BEF|AFT|BET|EST|CAL|INT)\s+)?(?:(?<day>[0-9]{1,2})\s+)?(?:(?<month>JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+)?(?<year>[0-9]{4})?( |$)/';

    const MONTHS = 'JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC';

    /** @var string */
    private $value;
    /** @var int */
    private $timestamp;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toTimestamp()
    {
        return '';
    }

    public function __toString()
    {
        return $this->value;
    }

    public static function gedcomDateToTimestamp($gedcomDateString)
    {
        if (preg_match(self::DATE_REGEXP, $gedcomDateString, $matches)) {
            $matches[2] = str_replace(explode('|', self::MONTHS), explode('|', '1|2|3|4|5|6|7|8|9|10|11|12'), $matches[2]);
            $matches[2] = $matches[2] ? $matches[2] : '1';
            $matches[1] = $matches[1] ? $matches[1] : '1';
            return strtotime("{$matches[3]}-{$matches[2]}-{$matches[1]}");
        } else {
            return strtotime($gedcomDateString);
        }
    }
}
