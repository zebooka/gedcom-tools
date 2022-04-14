<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Indi;
use Zebooka\Gedcom\Model\IndiRanking;
use Zebooka\Gedcom\Service\LeafsService;

class LeafsServiceTest extends TestCase
{
    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../../res/gedcom.ged'));
    }

    public function gedcomToLeafsLadderProvider()
    {
        return [
            'Equal grands' => ['GRANDFATHER', 0, 'GRANDMOTHER'],
            'Grand less then father' => ['GRANDFATHER', -1, 'FATHER'],
            'Father with his parents is higher that mother without' => ['FATHER', 1, 'MOTHER'],
            'Dead is less than living' => ['UNCLE', -1, 'FATHER'],
            'Father having grands is less than son' => ['FATHER', -1, 'SON'],
            'Mother is less then son' => ['MOTHER', -1, 'SON'],
            'Father less than daughter having grand daughter' => ['FATHER', -1, 'DAUGHTER'],
            'Mother less than daughter having grand daughter' => ['MOTHER', -1, 'DAUGHTER'],
            'Son equals daughter regardless her grand daughter' => ['SON', 0, 'DAUGHTER'],
            'Daughter is less than grand daughter' => ['DAUGHTER', -1, 'GRANDDAUGHTER'],
        ];
    }

    /**
     * @dataProvider gedcomToLeafsLadderProvider
     */
    public function test_gedcomToLeafs($a, $eq, $b)
    {
        $service = new LeafsService();
        $indiRankings = $service->gedcomToIndiRankings($g = $this->gedcom());
        $this->assertIsArray($indiRankings);
        $this->assertCount(8, $indiRankings);
        $this->assertContainsOnlyInstancesOf(IndiRanking::class, $indiRankings);
        $this->assertEquals($eq, $indiRankings[$a]->ranking() <=> $indiRankings[$b]->ranking());
    }

    public function test_rankingFormula()
    {
        $service = new LeafsService();
        $this->assertEqualsWithDelta((1 + sqrt(1 * 1 + 2 * 2) + log(3)), $service->rankingFormula(1, 2, 3, false), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta((1 + sqrt(1.5 * 1.5 + 2.1 * 2.1) + log(2)) * 0.95, $service->rankingFormula(1.5, 2.1, 2, true), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta((1 + sqrt(4 * 4 + 0.95 * 0.95) + log(1)), $service->rankingFormula(4, 0.95, 1, false), PHP_FLOAT_EPSILON);
    }
}
