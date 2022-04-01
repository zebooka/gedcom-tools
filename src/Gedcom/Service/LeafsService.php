<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

class LeafsService
{

    public function gedcomToLeafs(Document $gedcom)
    {
        // add all nodes to heap
        $list = [];
        foreach ($gedcom->indi() as $indi) {
            /** @var \DOMElement $indi */
            $list[$indi->getAttribute('id')] = [$this->calculateRanking($indi, $gedcom), $indi, $this->checkIsLeaf($indi, $gedcom)];
        }

        uasort(
            $list,
            function ($a, $b) {
                return $a[0] <=> $b[0];
            }
        );

        return $list;
    }

    private function checkIsLeaf(\DOMElement $indi, Document $gedcom)
    {
        $id = $indi->getAttribute('id');

        // not dead
        $notDead = !$gedcom->xpath('./G:DEAT', $indi)->count();

        // less than 30 years since birth
        $youngerThan30 = false;
        if ($birt = $gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $indi)) {
            $timestamp = strtotime('+ 30 years', Document::gedcomDateToTimestamp($birt));
            $youngerThan30 = $timestamp > time();
        }

        // no children
        $noChildren = true;
        $fams = $gedcom->xpath("/G:GEDCOM/G:FAM[G:HUSB/@value='{$id}']|/G:GEDCOM/G:FAM[G:WIFE/@value='{$id}']");
        if ($fams) {
            foreach ($fams as $fam) {
                if ($gedcom->xpath('./G:CHIL', $fam)->count()) {
                    $noChildren = false;
                    break;
                }
            }
        }

        return $notDead && $youngerThan30 && $noChildren;
    }

    private function calculateRanking(\DOMElement $indi, Document $gedcom)
    {
        $id = $indi->getAttribute('id');

        $basic = 1;
        $sublings = 1;
        $father = $mother = 0;
        $cp = 1; // coefficient for parents weight

        // get family where indi is child
        $fam = $gedcom->xpath("/G:GEDCOM/G:FAM[G:CHIL/@value='{$id}']")->item(0);

        if ($fam) {
            $sublings = $gedcom->xpath('./G:CHIL', $fam)->count();

            // calculate parents and their rankings
            if ($f = $gedcom->xpath('string(./G:HUSB/@value)', $fam)) {
                if ($f = $gedcom->xpath("/G:GEDCOM/G:INDI[@id='{$f}']")->item(0)) {
                    /** @var \DOMElement $f */
                    $father = $this->calculateRanking($f, $gedcom);
                }
            }

            if ($m = $gedcom->xpath('string(./G:WIFE/@value)', $fam)) {
                if ($m = $gedcom->xpath("/G:GEDCOM/G:INDI[@id='{$m}']")->item(0)) {
                    /** @var \DOMElement $m */
                    $mother = $this->calculateRanking($m, $gedcom);
                }
            }
        }

        return $basic + sqrt($father * $father + $mother * $mother) * $cp + log($sublings);
    }
}
