<?php

namespace Zebooka\Gedcom\Document;

trait IndiTrait
{
    use DocumentTrait,
        EscapeTrait;

    /**
     * Returns either one specific INDI node or all INDI nodes at once.
     * @return \DOMElement|\DOMNode|\DOMNodeList|null
     */
    public function indiNode(?string $xref = null)
    {
        if ($xref !== null && $xref !== '') {
            self::validateXref($xref);
            return $this->xpath('/G:GEDCOM/G:INDI[@xref="' . ($xref) . '"]')->item(0);
        } else {
            return $this->xpath('/G:GEDCOM/G:INDI');
        }
    }
}
