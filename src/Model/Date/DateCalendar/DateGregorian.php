<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Fisharebest\ExtCalendar\GregorianCalendar;
use Zebooka\Gedcom\Model\Date\DateInterface;
use Zebooka\Gedcom\Model\Date\YearInterface;

class DateGregorian implements DateInterface, DateCalendarInterface, YearInterface
{
    const CALENDAR_5 = '@#DGREGORIAN@';
    const CALENDAR_7 = 'GREGORIAN';

    const EPOCH_5 = 'B.C.';
    const EPOCH_7 = 'BCE';

    const REGEXP = '/^(?:(?<calendar>GREGORIAN|@#DGREGORIAN@)\s+)?(?:(?:(?<day>0?[1-9]|[1-2][0-9]|30|31)\s+)?(?<month>JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+)?(?<year>\d+)(?:\s*(?<epoch>BCE|B\\.C\\.))?$/';

    /** @var string|null */
    private $calendar;

    /** @var int|null */
    private $day;

    /** @var string|null */
    private $month;

    /** @var int|null */
    private $monthDigital;

    /** @var int */
    private $year;

    /** @var string|null */
    private $epoch;

    public function __construct(int $year, ?string $month = null, ?int $day = null, ?string $calendar = null, ?string $epoch = null)
    {
        if (!$month && $day) {
            throw new \UnexpectedValueException('Day without month is not allowed.');
        }

        if (!$year) {
            throw new \UnexpectedValueException('Incorrect year supplied.');
        }

        if (!in_array($calendar, [null, self::CALENDAR_5, self::CALENDAR_7])) {
            throw new \UnexpectedValueException("Unsupported calendar value '{$calendar}'.");
        }

        $months = [null, 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        if (!in_array($month, $months)) {
            throw new \UnexpectedValueException("Unsupported month '{$month}'.");
        }

        if (!in_array($epoch, [null, self::EPOCH_5, self::EPOCH_7])) {
            throw new \UnexpectedValueException("Unsupported epoch value '{$epoch}'.");
        }

        $this->year = $year;
        $this->month = $month;
        $this->monthDigital = array_search($month, $months);
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->calendar = $calendar;
        $this->epoch = $epoch;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode gregorian calendar date string '{$string}'.");
        }

        return new self(
            (int)$m['year'],
            !empty($m['month']) ? $m['month'] : null,
            !empty($m['day']) ? (int)$m['day'] : null,
            $m['calendar'] ?? null,
            $m['epoch'] ?? null
        );
    }

    public function __toString(): string
    {
        return ($this->calendar ? $this->calendar . ' ' : '')
            . ($this->month ? ($this->day ? $this->day . ' ' : '') . $this->month . ' ' : '')
            . $this->year
            . ($this->epoch ? ($this->epoch === self::EPOCH_7 ? ' ' : '') . $this->epoch : '');
    }

    public function toTimestamp(): ?int
    {
        // do not use mktime() because it does not correctly support years < 1000
        $gc = new GregorianCalendar();
        $julianDay = $gc->ymdToJd(($this->year > 0 && $this->epoch) ? -$this->year : $this->year, $this->monthDigital ?: 1, $this->day ?: 1);
        return ($julianDay - 2440588) * 86400;
    }

    public function year(): int
    {
        return $this->year;
    }
}
