<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Leaf;
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
            ['GRANDFATHER', 0, 'GRANDMOTHER', 1],
            ['GRANDFATHER', -1, 'FATHER', 1 + sqrt(2)],
            ['FATHER', 1, 'MOTHER', 1],
            ['FATHER', -1, 'SON', 1 + sqrt(pow(1 + sqrt(2), 2) + 1) + log(2)],
            ['MOTHER', -1, 'SON', 1 + sqrt(pow(1 + sqrt(2), 2) + 1) + log(2)],
            ['FATHER', -1, 'DAUGHTER', 1 + sqrt(pow(1 + sqrt(2), 2) + 1) + log(2)],
            ['MOTHER', -1, 'DAUGHTER', 1 + sqrt(pow(1 + sqrt(2), 2) + 1) + log(2)],
            ['SON', 0, 'DAUGHTER', 1 + sqrt(pow(1 + sqrt(2), 2) + 1) + log(2)],
            ['DAUGHTER', -1, 'GRANDDAUGHTER', 1 + sqrt(pow(1 + sqrt(pow(1 + sqrt(2), 2) + 1) + log(2), 2))],
        ];
    }

    /**
     * @dataProvider gedcomToLeafsLadderProvider
     */
    public function test_gedcomToLeafs($a, $eq, $b, $ranking)
    {
        $service = new LeafsService();
        $leafs = $service->gedcomToLeafs($g = $this->gedcom());
        $this->assertIsArray($leafs);
        $this->assertCount(7, $leafs);
        $this->assertContainsOnlyInstancesOf(Leaf::class, $leafs);
        $this->assertEquals($eq, $leafs[$a]->ranking <=> $leafs[$b]->ranking);
        $this->assertEqualsWithDelta($ranking, $leafs[$b]->ranking, 0.0000001);
    }
}
