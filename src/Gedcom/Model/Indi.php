<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendarInterface;

class Indi
{
    /** @var Document */
    public $gedcom;

    /** @var \DOMElement */
    public $node;

    public function __construct(\DOMElement $node, Document $gedcom)
    {
        if ($node->nodeName !== 'INDI') {
            throw new \UnexpectedValueException("Unexpected node name supplied. Expecting INDI.");
        }
        $this->gedcom = $gedcom;
        $this->node = $node;
    }

    public function node(): \DOMElement
    {
        return $this->node;
    }

    public function gedcom(): Document
    {
        return $this->gedcom;
    }

    public function xref(): string
    {
        return $this->node->getAttribute('xref');
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

    public function hasNoChildren(): bool
    {
        $xref = $this->xref();
        $fams = $this->gedcom->xpath("/G:GEDCOM/G:FAM[G:HUSB/@value='{$xref}']|/G:GEDCOM/G:FAM[G:WIFE/@value='{$xref}']");
        if ($fams->count()) {
            foreach ($fams as $fam) {
                if ($this->gedcom->xpath('./G:CHIL', $fam)->count()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function isLeaf(): bool
    {
        return !$this->isDead() && $this->isYoungerThan30() && $this->hasNoChildren();
    }
}
