<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateFactory;
use Zebooka\Gedcom\Model\Leaf;

class LeafsService
{
    public function gedcomToLeafs(Document $gedcom)
    {
        // add all nodes to heap
        $list = [];
        foreach ($gedcom->indi() as $indi) {
            /** @var \DOMElement $indi */
            $list[$indi->getAttribute('xref')] = new Leaf(
                $indi,
                $this->calculateRanking($indi, $gedcom),
                $this->checkLeafIsFresh($indi, $gedcom)
            );
        }

        uasort(
            $list,
            function (Leaf $a, Leaf $b) {
                return $a->ranking <=> $b->ranking;
            }
        );

        return $list;
    }

    /**
     * Leaf is considered fresh if it has no children yet, younger that 30 years and not dead.
     * @param \DOMElement $indi
     * @param Document $gedcom
     * @return bool
     */
    public function checkLeafIsFresh(\DOMElement $indi, Document $gedcom)
    {
        $xref = $indi->getAttribute('xref');

        // not dead
        $notDead = !$gedcom->xpath('./G:DEAT', $indi)->count();

        // less than 30 years since birth
        $youngerThan30 = false;
        if ($birt = $gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $indi)) {
            $birtDate = DateFactory::fromString($birt);
            if ($birtDate instanceof DateCalendarInterface) {
                $youngerThan30 = ($birtDate->toTimestamp() > strtotime('-30 years'));
            } else {
                $youngerThan30 = false;
            }
        }

        // no children
        $noChildren = true;
        $fams = $gedcom->xpath("/G:GEDCOM/G:FAM[G:HUSB/@value='{$xref}']|/G:GEDCOM/G:FAM[G:WIFE/@value='{$xref}']");
        if ($fams->count()) {
            foreach ($fams as $fam) {
                if ($gedcom->xpath('./G:CHIL', $fam)->count()) {
                    $noChildren = false;
                    break;
                }
            }
        }

        return $notDead && $youngerThan30 && $noChildren;
    }

    public function calculateRanking(\DOMElement $indi, Document $gedcom)
    {
        $xref = $indi->getAttribute('xref');

        $basic = 1;
        $sublings = 1;
        $father = $mother = 0;
        $cp = 1; // coefficient for parents weight

        // get family where indi is child
        $fam = $gedcom->xpath("/G:GEDCOM/G:FAM[G:CHIL/@pointer='{$xref}']")->item(0);

        if ($fam) {
            $sublings = $gedcom->xpath('./G:CHIL', $fam)->count();

            // calculate parents and their rankings
            if ($fxref = $gedcom->xpath('string(./G:HUSB/@pointer)', $fam)) {
                if ($f = $gedcom->xpath("/G:GEDCOM/G:INDI[@xref='{$fxref}']")->item(0)) {
                    /** @var \DOMElement $f */
                    $father = $this->calculateRanking($f, $gedcom);
                }
            }

            if ($mxref = $gedcom->xpath('string(./G:WIFE/@pointer)', $fam)) {
                if ($m = $gedcom->xpath("/G:GEDCOM/G:INDI[@xref='{$mxref}']")->item(0)) {
                    /** @var \DOMElement $m */
                    $mother = $this->calculateRanking($m, $gedcom);
                }
            }
        }

        return $basic + sqrt($father * $father + $mother * $mother) * $cp + log($sublings);
    }
}
