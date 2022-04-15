<?php

namespace Zebooka\Gedcom\Document;

trait IndiTrait
{
    use DocumentTrait,
        EscapeTrait;

    public function indi($xref = null)
    {
        if ($xref !== null && $xref !== '') {
            self::validateXref($xref);
            return $this->xpath('/G:GEDCOM/G:INDI[@xref="' . ($xref) . '"]')->item(0);
        } else {
            return $this->xpath('/G:GEDCOM/G:INDI');
        }
    }
}
