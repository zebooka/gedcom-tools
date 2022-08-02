<?php

namespace Zebooka\Gedcom\Model\Date;

class DateEmpty implements DateInterface
{
    const REGEXP = '/^\s*$/';

    public function __construct()
    {
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode empty date string '{$string}'.");
        }

        return new self();
    }

    public function __toString(): string
    {
        return '';
    }
}
