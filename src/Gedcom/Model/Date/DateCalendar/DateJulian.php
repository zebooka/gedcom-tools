<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Zebooka\Gedcom\Model\Date\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateInterface;

class DateJulian implements DateInterface, DateCalendarInterface
{
    const CALENDAR_5 = '@#DJULIAN@';
    const CALENDAR_7 = 'JULIAN';

    const EPOCH_5 = 'B.C.';
    const EPOCH_7 = 'BCE';

    const REGEXP = '/^(?:(?<calendar>JULIAN|@#DJULIAN@)\s+)(?:(?:(?<day>0?[1-9]|[1-2][0-9]|30|31)\s+)?(?<month>JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+)?(?<year>\d+)(?:\s*(?<epoch>BCE|B\\.C\\.))?$/';

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

    public function __construct(int $year, string $month = null, int $day = null, string $calendar = self::CALENDAR_5, string $epoch = null)
    {
        if (!$month && $day) {
            throw new \UnexpectedValueException('Day without month is not allowed.');
        }

        if (!$year) {
            throw new \UnexpectedValueException('Incorrect year supplied.');
        }

        if (!in_array($calendar, [self::CALENDAR_5, self::CALENDAR_7])) {
            throw new \UnexpectedValueException("Unsupported calendar value '{$calendar}'.");
        }

        if (!in_array($month, [null, 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'])) {
            throw new \UnexpectedValueException("Unsupported month '{$month}'.");
        }

        if (!in_array($epoch, [null, self::EPOCH_5, self::EPOCH_7])) {
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
            throw new \UnexpectedValueException("Unable to decode julian calendar date string '{$string}'.");
        }

        return new self(
            (int)$m['year'],
            $m['month'] ?? null,
            !empty($m['day']) ? (int)$m['day'] : null,
            $m['calendar'],
            $m['epoch'] ?? null
        );
    }

    public function __toString(): string
    {
        return $this->calendar . ' '
            . ($this->month ? ($this->day ? $this->day . ' ' : '') . $this->month . ' ' : '')
            . $this->year
            . ($this->epoch ? ($this->epoch === self::EPOCH_7 ? ' ' : '') . $this->epoch : '');
    }
}
