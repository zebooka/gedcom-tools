<?php

namespace Zebooka\Gedcom\Document;

trait DocumentTrait
{
    abstract public function sxml($node = null);

    abstract public function dom();

    abstract public function xpath($expression, \DOMNode $contextNode = null);
}
