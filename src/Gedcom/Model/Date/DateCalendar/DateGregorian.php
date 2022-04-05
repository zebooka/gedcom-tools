<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Zebooka\Gedcom\Model\DateInterface;

class DateGregorian implements DateInterface
{
    const CALENDAR_5 = '@#DGREGORIAN@';
    const CALENDAR_7 = 'GREGORIAN';

    const REGEXP = '/^(?:(GREGORIAN|@#DGREGORIAN@)\s+)?(?:(?:(?<day>\d+)\s)?(?<month>[A-Z0-9_]+)\s+)?(?<year>\d+)(?:\s+(?<epoch>(?:BCE|_[A-Z0-9_]+)))?$/';

    /** @var string|null */
    private $calendar;

    /** @var int|null */
    private $day;

    /** @var string|null */
    private $month;

    /** @var int */
    private $year;

    /** @var string|null */
    private $epoch;

    public function __construct(int $year, string $month = null, int $day = null, string $calendar = null, string $epoch = null)
    {
        if (!$month && $day) {
            throw new \UnexpectedValueException('Day without month is not allowed.');
        }

        if (!in_array($calendar, [null, self::CALENDAR_5, self::CALENDAR_7])) {
            throw new \UnexpectedValueException("Unsupported calendar value '{$calendar}'.");
        }

        if (!in_array($month, ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'])) {
            throw new \UnexpectedValueException("Unsupported month '{$month}'.");
        }

        if (!in_array($epoch, [null, 'TODO'])) {
            throw new \UnexpectedValueException("Unsupported epoch value '{$epoch}'.");
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->calendar = $calendar;
        $this->epoch = $epoch;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode gregorian date string '{$string}'.");
        }
        return new self(
            (int)$m['year'],
            $m['month'] ?? null,
            (int)$m['day'] ?? null,
            $m['calendar'] ?? null,
            $m['epoch'] ?? null
        );
    }

    public function __toString(): string
    {
        return ($this->calendar ? $this->calendar . ' ' : '')
            . ($this->month ? ($this->day ? $this->day . ' ' : '') . $this->month . ' ' : '')
            . $this->year
            . ($this->epoch ? ' ' . $this->epoch : ''); // TODO epoch for 5
    }
}
