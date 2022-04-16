<?php

namespace Zebooka\Gedcom\Model\Date;

class DateInt implements DateCalendarInterface
{
    const REGEXP = '/^INT\\s+(?<int>.+)\s+(?<phrase>\\(.+\\))$/';

    private $interpretered;
    private $phrase;

    public function __construct(DateCalendarInterface $interpretered, DatePhrase $phrase)
    {
        $this->interpretered = $interpretered;
        $this->phrase = $phrase;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode int date string '{$string}'.");
        }

        return new self(
            DateCalendar::fromString($m['int']),
            DatePhrase::fromString($m['phrase'])
        );
    }

    public function __toString(): string
    {
        return "INT {$this->interpretered} {$this->phrase}";
    }

    public function toTimestamp(): ?int
    {
        return $this->interpretered->toTimestamp();
    }

    public function year(): string
    {
        return $this->interpretered->year();
    }
}
