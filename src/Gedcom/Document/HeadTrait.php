<?php

namespace Zebooka\Gedcom\Document;

trait HeadTrait
{
    use DocumentTrait;

    public function head()
    {
        return $this->xpath('/G:GEDCOM/G:HEAD');
    }
}
