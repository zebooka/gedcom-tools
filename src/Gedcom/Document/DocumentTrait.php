<?php

namespace Zebooka\Gedcom\Document;

trait DocumentTrait
{
    abstract public function sxml($node = null);

    abstract public function dom();

    abstract public function xpath($expression, \DOMNode $contextNode = null);

    /**
     * Returns node by xref.
     * @return \DOMElement|\DOMNode|null
     */
    public function node(string $xref)
    {
        self::validateXref($xref);
        return $this->xpath('/G:GEDCOM/G:*[@xref="' . ($xref) . '"]')->item(0);
    }
}
