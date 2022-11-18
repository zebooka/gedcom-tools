<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\DateInterface;

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
        if ($birt = $this->birt()){
            if ($birt instanceof DateCalendarInterface) {
                return ($birt->toTimestamp() > strtotime('-30 years'));
            }
        }
        return false;
    }

    protected function date($tag): ?DateInterface
    {
        $date = $this->gedcom->xpath("string(./G:{$tag}/G:DATE/@value)", $this->node);
        return isset($date) ? DateFactory::fromString($date) : null;
    }

    public function birt(): ?DateInterface
    {
        return $this->date('BIRT');
    }

    public function deat(): ?DateInterface
    {
        return $this->date('DEAT');
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
