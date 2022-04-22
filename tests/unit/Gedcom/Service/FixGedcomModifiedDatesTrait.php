<?php

namespace Test\Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

trait FixGedcomModifiedDatesTrait
{
    protected function fixGedcomModifiedDate(Document $gedcom)
    {
        foreach ($gedcom->xpath('/G:GEDCOM/G:HEAD/G:SOUR | /G:GEDCOM/*/G:CHAN') as $node) {
            /** @var \DOMElement $node */
            $node->remove();
        }
    }
}
