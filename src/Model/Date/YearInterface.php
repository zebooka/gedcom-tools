<?php

namespace Zebooka\Gedcom\Model\Date;

interface YearInterface
{
    /**
     * A function that returns year of date, either precise or approximate. If date is range/period, then the first not null is returned.
     * @return int|null
     */
    public function year(): ?int;
}
