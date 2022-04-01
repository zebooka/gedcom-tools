<?php

namespace Zebooka\Gedcom\Model\Date;

class DatePeriod
{
    const FROM = 'FROM';
    const TO = 'TO';

    const REGEXP = '/^(?:FROM\s+(?<from>.+))?(^|\s+|$)(?:TO\s+(?<to>.+))?$/';

    private $from;
    private $to;

    public function __construct(DateExact $from = null, DateExact $to = null)
    {
        if (!$from && !$to) {
            throw new \UnexpectedValueException('Please supply any of FROM/TO dates (or both) for period.');
        }
        $this->from = $from;
        $this->to = $to;
    }

    public static function fromString($string)
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode period date string '{$string}'.");
        }

        return new self(
            !empty($m['from']) ? DateExact::fromString($m['from']) : null,
            !empty($m['to']) ? DateExact::fromString($m['to']) : null
        );
    }

    public function __toString()
    {
        return ($this->from ? "FROM {$this->from}" : '')
            . ($this->from && $this->to ? ' ' : '')
            . ($this->to ? "TO {$this->to}" : '');
    }
}
