<?php

namespace Zebooka\Gedcom\Model\Date;

use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;

class DatePeriod implements DateInterface, YearInterface
{
    const REGEXP = '/^(?:FROM\s+(?<from>.+?))?(^|\s+|$)(?:TO\s+(?<to>.+))?$/';

    private $from;
    private $to;

    public function __construct(DateCalendarInterface $from = null, DateCalendarInterface $to = null)
    {
        if (!$from && !$to) {
            throw new \UnexpectedValueException('Please supply any of FROM/TO dates (or both) for period.');
        }
        $this->from = $from;
        $this->to = $to;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode period date string '{$string}'.");
        }

        return new self(
            !empty($m['from']) ? DateCalendar::fromString($m['from']) : null,
            !empty($m['to']) ? DateCalendar::fromString($m['to']) : null
        );
    }

    public function __toString(): string
    {
        return ($this->from ? "FROM {$this->from}" : '')
            . ($this->from && $this->to ? ' ' : '')
            . ($this->to ? "TO {$this->to}" : '');
    }

    public function year(): ?int
    {
        return ($this->from instanceof YearInterface ? $this->from->year() : ($this->to instanceof YearInterface ? $this->to->year() : null));
    }
}
