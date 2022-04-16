<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Indi;
use Zebooka\Gedcom\Model\IndiRanking;

class LeafsService
{
    private $rankingsCache = [];

    /**
     * @param Document $gedcom
     * @return IndiRanking[]
     */
    public function gedcomToIndiRankings(Document $gedcom)
    {
        // add all nodes to heap
        $list = [];
        foreach ($gedcom->indiNode() as $node) {
            /** @var \DOMElement $node */
            $indi = new Indi($node, $gedcom);
            $list[$node->getAttribute('xref')] = new IndiRanking($indi, $this->calculateRanking($indi, $gedcom));
        }

        uasort(
            $list,
            function (IndiRanking $a, IndiRanking $b) {
                return $a->ranking() <=> $b->ranking();
            }
        );

        return $list;
    }

    public function calculateRanking(Indi $indi)
    {
        $gedcom = $indi->gedcom();
        $xref = $indi->xref();

        if (isset($this->rankingsCache[$xref])) {
            return $this->rankingsCache[$xref];
        }

        $deatPenalty = 0.95;
        $basic = 1;
        $sublingsCount = 1;
        $fatherRanking = $motherRanking = 0;
        $cp = 1; // coefficient for parents weight

        // get family where indi is child
        $fam = $gedcom->xpath("/G:GEDCOM/G:FAM[G:CHIL/@pointer='{$xref}']")->item(0);

        if ($fam) {
            $sublingsCount = $gedcom->xpath('./G:CHIL', $fam)->count();

            // calculate parents and their rankings
            if ($fxref = $gedcom->xpath('string(./G:HUSB/@pointer)', $fam)) {
                if ($f = $gedcom->xpath("/G:GEDCOM/G:INDI[@xref='{$fxref}']")->item(0)) {
                    /** @var \DOMElement $f */
                    $fatherRanking = $this->calculateRanking(new Indi($f, $gedcom));
                }
            }

            if ($mxref = $gedcom->xpath('string(./G:WIFE/@pointer)', $fam)) {
                if ($m = $gedcom->xpath("/G:GEDCOM/G:INDI[@xref='{$mxref}']")->item(0)) {
                    /** @var \DOMElement $m */
                    $motherRanking = $this->calculateRanking(new Indi($m, $gedcom));
                }
            }
        }

        return $this->rankingsCache[$xref] = $this->rankingFormula($fatherRanking, $motherRanking, $sublingsCount, $indi->isDead());
    }

    public function rankingFormula(float $fatherRanking, float $motherRanking, int $sublingsCount, bool $isDead)
    {
        return (1 + sqrt($fatherRanking * $fatherRanking + $motherRanking * $motherRanking) + log($sublingsCount)) * ($isDead ? 0.95 : 1);
    }
}
