<?php

namespace Zebooka\Gedcom\Model\Date;

use Zebooka\Gedcom\Model\DateInterface;

interface DateCalendarInterface extends DateInterface
{
    public static function fromString(string $string);

    public function __toString(): string;

    public function toTimestamp(): ?int;
}
