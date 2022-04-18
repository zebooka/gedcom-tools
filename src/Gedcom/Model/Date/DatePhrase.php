<?php

namespace Zebooka\Gedcom\Model\Date;

use Zebooka\Gedcom\Model\Date\DateInterface;

class DatePhrase implements DateInterface
{
    const REGEXP = '/^\\((?<phrase>.*)\\)$/';

    private $phrase;

    public function __construct(string $phrase)
    {
        $this->phrase = $phrase;
    }

    public static function fromString(string $string): self
    {
        if (!preg_match(self::REGEXP, $string, $m)) {
            throw new \UnexpectedValueException("Unable to decode phrase date string '{$string}'.");
        }

        return new self($m['phrase']);
    }

    public function __toString(): string
    {
        return "({$this->phrase})";
    }
}
