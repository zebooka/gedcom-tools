<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendarInterface;

class Indi extends NodeAbstract
{
    public function __construct(\DOMElement $node, Document $gedcom)
    {
        if ($node->nodeName !== 'INDI') {
            throw new \UnexpectedValueException("Unexpected node name supplied. Expecting INDI.");
        }
        parent::__construct($node, $gedcom);
    }

    public function isDead(): bool
    {
        return $this->gedcom->xpath('./G:DEAT', $this->node)->count() > 0;
    }

    public function isYoungerThan30(): bool
    {
        if ($birt = $this->gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $this->node)) {
            $birtDate = DateFactory::fromString($birt);
            if ($birtDate instanceof DateCalendarInterface) {
                return ($birtDate->toTimestamp() > strtotime('-30 years'));
            }
        }
        return false;
    }

    public function children()
    {
        $result = [];
        $xref = $this->xref();
        $famc = $this->gedcom->xpath("/G:GEDCOM/G:FAM[G:HUSB/@pointer='{$xref}']/G:CHIL|/G:GEDCOM/G:FAM[G:WIFE/@pointer='{$xref}']/G:CHIL");
        foreach ($famc as $chil) {
            /** @var \DOMElement $chil */
            $result[] = new Indi($this->gedcom->indiNode($chil->getAttribute('pointer')), $this->gedcom);
        }

        return $result;
    }

    public function hasChildren(): int
    {
        $xref = $this->xref();
        $famc = $this->gedcom->xpath("/G:GEDCOM/G:FAM[G:HUSB/@pointer='{$xref}']/G:CHIL|/G:GEDCOM/G:FAM[G:WIFE/@pointer='{$xref}']/G:CHIL");
        $nchi = $this->gedcom->xpath('string(./G:NCHI/@value)', $this->node);
        return max($famc->count(), $nchi);
    }

    public function isLeaf(): bool
    {
        return !$this->isDead() && $this->isYoungerThan30() && !$this->hasChildren();
    }
}
