<?php

namespace Test\Zebooka\Gedcom\Service;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\DateInterface;
use Zebooka\Gedcom\Model\IndiRanking;
use Zebooka\Gedcom\Service\LeafsService;

class LeafsServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../res/gedcom.ged'));
    }

    public function gedcomToLeafsLadderProvider()
    {
        return [
            'Equal grands' => ['GRANDFATHER', -1, 'GRANDMOTHER'],
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
        $this->assertCount(9, $indiRankings);
        $this->assertContainsOnlyInstancesOf(IndiRanking::class, $indiRankings);
        $this->assertEquals(
            $eq,
            $indiRankings[$a]->ranking() <=> $indiRankings[$b]->ranking(),
            "{$indiRankings[$a]->ranking()} <=> {$indiRankings[$b]->ranking()}"
        );
    }

    public function test_rankingFormula()
    {
        $service = new LeafsService();
        $this->assertEqualsWithDelta(
            (1 + sqrt(1 * 1 + 2 * 2) + log(3)),
            $service->rankingFormula(1, 2, 3, false, null, null),
            PHP_FLOAT_EPSILON
        );
        $this->assertEqualsWithDelta(
            (1 + sqrt(1.5 * 1.5 + 2.1 * 2.1) + log(2)) * 0.95 * 1.02,
            $service->rankingFormula(1.5, 2.1, 2, true, \Mockery::mock(DateCalendarInterface::class), null),
            PHP_FLOAT_EPSILON
        );
        $this->assertEqualsWithDelta(
            (1 + sqrt(1.5 * 1.5 + 2.1 * 2.1) + log(2)) * 0.95 * 1.02 * 1.01,
            $service->rankingFormula(1.5, 2.1, 2, true, \Mockery::mock(DateCalendarInterface::class), \Mockery::mock(DateInterface::class)),
            PHP_FLOAT_EPSILON
        );
        $this->assertEqualsWithDelta(
            (1 + sqrt(4 * 4 + 0.95 * 0.95) + log(1)),
            $service->rankingFormula(4, 0.95, 1, false, null, null),
            PHP_FLOAT_EPSILON
        );
        $this->assertEqualsWithDelta(
            (1 + sqrt(1.5 * 1.5 + 2.1 * 2.1) + log(2)) * 1.01,
            $service->rankingFormula(1.5, 2.1, 2, false, \Mockery::mock(DateInterface::class), null),
            PHP_FLOAT_EPSILON
        );
    }
}
