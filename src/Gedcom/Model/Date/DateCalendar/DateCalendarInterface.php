<?php

namespace Zebooka\Gedcom\Model\Date\DateCalendar;

use Zebooka\Gedcom\Model\Date\DateInterface;

interface DateCalendarInterface extends DateInterface
{
    public static function fromString(string $string);

    public function __toString(): string;

    public function toTimestamp(): ?int;

    public function year(): string;
}
