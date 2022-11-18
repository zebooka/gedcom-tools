<?php

namespace Zebooka\Gedcom\Model\Date;

interface DateInterface
{
    public static function fromString(string $string);

    public function __toString(): string;
}
