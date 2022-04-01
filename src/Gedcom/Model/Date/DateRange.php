<?php

namespace Zebooka\Gedcom\Model\Date;

class DateRange
{
    const BET = 'BET';
    const AND = 'AND';
    const BEF = 'BEF';
    const AFT = 'AFT';

    const REGEXP = '/^(?:(BET\s+(?<between>.+)\s+AND\s+(?<and>.+))|(BEF\s+(?<before>.+))|(AFT\s+(?<after>.+)))?$/';

    private $after;
    private $before;

    public function __construct(DateExact $after = null, DateExact $before = null)
    {
        if (!$after && !$before) {
            throw new \UnexpectedValueException('Please supply any of AFT/BEF dates (or both) for period.');
        }
        $this->after = $after;
        $this->before = $before;
    }

    public static function fromString($string)
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode period date string '{$string}'.");
        }

        if (!empty($m['between']) && !empty($m['and'])) {
            return new self(
                DateExact::fromString($m['between']),
                DateExact::fromString($m['and'])
            );
        } else {
            return new self(
                !empty($m['after']) ? DateExact::fromString($m['after']) : null,
                !empty($m['before']) ? DateExact::fromString($m['before']) : null
            );
        }
    }

    public function __toString()
    {
        if ($this->after && $this->before) {
            return "BET {$this->after} AND {$this->before}";
        } elseif ($this->after) {
            return "AFT {$this->after}";
        } elseif ($this->before) {
            return "BEF {$this->before}";
        } else {
            throw new \RuntimeException('Unexpected situation. Both dates for DateRange are empty.');
        }
    }
}
