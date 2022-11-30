<?php

namespace Zebooka\Gedcom\Model;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Indi;
use Zebooka\Gedcom\Model\IndiRanking;

class IndiRankingTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test()
    {
        /** @var Indi $indiMock */
        $indiMock = \Mockery::mock(Indi::class);
        $indiRanking = new IndiRanking($indiMock, 123.45);
        $this->assertSame($indiMock, $indiRanking->indi());
        $this->assertEqualsWithDelta(123.45, $indiRanking->ranking(), PHP_FLOAT_EPSILON);
    }
}
