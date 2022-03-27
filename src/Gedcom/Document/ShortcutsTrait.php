<?php

namespace Zebooka\Gedcom\Document;

trait ShortcutsTrait
{
    public function head()
    {
        return $this->xpath('/G:GEDCOM/G:HEAD');
    }

    public function indi($id)
    {
        return $this->xpath('/G:GEDCOM/G:INDI[id="' . self::escapeXref($id) . '"]');
    }

    public function fam($id)
    {
        return $this->xpath('/G:GEDCOM/G:FAM[id="' . self::escapeXref($id) . '"]');
    }
}
