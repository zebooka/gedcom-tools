<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateApproximate;
use Zebooka\Gedcom\Model\Date\DateCalendar;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\YearInterface;

class DateApproxTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|null|DateCalendar
     */
    private function dateExactMock()
    {
        return \Mockery::mock(DateCalendarInterface::class, YearInterface::class)
            ->shouldReceive('__toString')
            ->andReturn('DATE_EXACT')
            ->getMock()
            ->shouldReceive('year')
            ->andReturn('123')
            ->getMock();
    }

    public function test_DateApprox_toString()
    {
        $this->assertEquals('ABT DATE_EXACT', (string)(new DateApproximate('ABT', $this->dateExactMock())));
        $this->assertEquals('CAL DATE_EXACT', (string)(new DateApproximate('CAL', $this->dateExactMock())));
        $this->assertEquals('EST DATE_EXACT', (string)(new DateApproximate('EST', $this->dateExactMock())));
    }

    public function test_DateApprox_year()
    {
        $this->assertEquals(123, (new DateApproximate('ABT', $this->dateExactMock()))->year());
        $this->assertEquals(123, (new DateApproximate('CAL', $this->dateExactMock()))->year());
        $this->assertEquals(123, (new DateApproximate('EST', $this->dateExactMock()))->year());
    }

    public function test_DateApprox_unknown_Lax_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("Only ABT/CAL/EST are allowed for approximate date. 'XXX' was supplied."));
        new DateApproximate('XXX', $this->dateExactMock());
    }

    public function test_DateApprox_empty_Lax_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("Only ABT/CAL/EST are allowed for approximate date. '' was supplied."));
        new DateApproximate(null, $this->dateExactMock());
    }
}
