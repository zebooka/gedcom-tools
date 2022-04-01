<?php

namespace Zebooka\Gedcom\Document;

trait IndiTrait
{
    use DocumentTrait,
        EscapeTrait;

    public function indi($id = null)
    {
        if ($id !== null && $id !== '') {
            return $this->xpath('/G:GEDCOM/G:INDI[id="' . self::escapeXref($id) . '"]');
        } else {
            return $this->xpath('/G:GEDCOM/G:INDI');
        }
    }
}
