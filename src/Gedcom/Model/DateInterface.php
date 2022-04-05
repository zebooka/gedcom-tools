<?php

namespace Zebooka\Gedcom\Model;

interface DateInterface
{
    public static function fromString(string $string);

    public function __toString(): string;
}
