<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Zebooka\Gedcom\Model\Date\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateInterface;

class DateHebrew implements DateInterface, DateCalendarInterface
{
    const CALENDAR_5 = '@#DHEBREW@';
    const CALENDAR_7 = 'HEBREW';

    const REGEXP = '/^(?:(?<calendar>HEBREW|@#DHEBREW@)\s+)(?:(?:(?<day>0?[1-9]|[1-2][0-9]|30)\s+)?(?<month>TSH|CSH|KSL|TVT|SHV|ADR|ADS|NSN|IYR|SVN|TMZ|AAV|ELL)\s+)?(?<year>\d+)$/';

    /** @var string|null */
    private $calendar;

    /** @var int|null */
    private $day;

    /** @var string|null */
    private $month;

    /** @var int */
    private $year;

    public function __construct(int $year, string $month = null, int $day = null, string $calendar = self::CALENDAR_5)
    {
        if (!$month && $day) {
            throw new \UnexpectedValueException('Day without month is not allowed.');
        }

        if ($year < 1) {
            throw new \UnexpectedValueException('Incorrect year supplied. Only positive years are supported.');
        }

        if (!in_array($calendar, [self::CALENDAR_5, self::CALENDAR_7])) {
            throw new \UnexpectedValueException("Unsupported calendar value '{$calendar}'.");
        }

        if (!in_array($month, [null, 'TSH', 'CSH', 'KSL', 'TVT', 'SHV', 'ADR', 'ADS', 'NSN', 'IYR', 'SVN', 'TMZ', 'AAV', 'ELL'])) {
            throw new \UnexpectedValueException("Unsupported month '{$month}'.");
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->calendar = $calendar;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode hebrew calendar date string '{$string}'.");
        }

        return new self(
            (int)$m['year'],
            $m['month'] ?? null,
            !empty($m['day']) ? (int)$m['day'] : null,
            $m['calendar']
        );
    }

    public function __toString(): string
    {
        return $this->calendar . ' '
            . ($this->month ? ($this->day ? $this->day . ' ' : '') . $this->month . ' ' : '')
            . $this->year;
    }
}
