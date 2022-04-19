<?php

namespace Zebooka\Gedcom\Document;

trait FamTrait
{
    use DocumentTrait,
        EscapeTrait;

    /**
     * Returns either one specific FAM node or all FAM nodes at once.
     * @return \DOMElement|\DOMNode|\DOMNodeList|null
     */
    public function famNode(?string $xref = null)
    {
        if ($xref !== null && $xref !== '') {
            self::validateXref($xref);
            return $this->xpath('/G:GEDCOM/G:FAM[@xref="' . ($xref) . '"]')->item(0);
        } else {
            return $this->xpath('/G:GEDCOM/G:FAM');
        }
    }
}
