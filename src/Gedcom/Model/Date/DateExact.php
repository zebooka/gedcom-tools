<?php

namespace Zebooka\Gedcom\Model\Date;

class DateExact
{
    const CAL_GREGORIAN = 'GREGORIAN';
    const CAL_JULIAN = 'JULIAN';
    const CAL_FRENCH_REVOLUTION = 'FRENCH_R';
    const CAL_HEBREW = 'HEBREW';

    const EPOCH_BEFORE_COMMON_ERA = 'BCE';

    const REGEXP = '/^(?:(?<calendar>GREGORIAN|JULIAN|FRENCH_R|HEBREW|_[A-Z0-9_]+)\s+)?(?:(?:(?<day>\d+)\s)?(?<month>[A-Z0-9_]+)\s+)?(?<year>\d+)(?:\s+(?<epoch>(?:BCE|_[A-Z0-9_]+)))?$/';

    private static $monthsList = [
        '' => ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
        self::CAL_GREGORIAN => ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
        self::CAL_JULIAN => ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
    ];

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

        if (!in_array($calendar, [null, self::CAL_GREGORIAN, self::CAL_JULIAN, self::CAL_FRENCH_REVOLUTION, self::CAL_HEBREW])) {
            throw new \UnexpectedValueException("Unsupported calendar value '{$calendar}'.");
        }

        if (!isset(self::$monthsList[$calendar]) && !in_array($month, self::$monthsList[$calendar])) {
            throw new \UnexpectedValueException("Unsupported month '{$month}' for calendar '{$calendar}'.");
        }

        if (!in_array($epoch, [null, self::EPOCH_BEFORE_COMMON_ERA]) && '_' !== $epoch[0]) {
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
            throw new \UnexpectedValueException("Unable to decode exact date string '{$string}'.");
        }
        return new self(
            (int) $m['year'],
            $m['month'] ?? null,
            (int) $m['day'] ?? null,
            $m['calendar'] ?? null,
            $m['epoch'] ?? null
        );
    }

    public function __toString(): string
    {
        return ($this->calendar ? $this->calendar . ' ' : '')
            . ($this->month ? ($this->day ? $this->day . ' ' : '') . $this->month . ' ' : '')
            . $this->year
            . ($this->epoch ? ' ' . $this->epoch : '');
    }
}
