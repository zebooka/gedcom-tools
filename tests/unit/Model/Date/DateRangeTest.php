<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\DateRange;

class DateRangeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|null|DateCalendar
     */
    private function dateExactMock($string)
    {
        return \Mockery::mock(DateCalendarInterface::class)
            ->shouldReceive('__toString')
            ->andReturn($string)
            ->getMock();
    }

    public function test_DateRange_toString()
    {
        $this->assertEquals('AFT DATE1', (string)(new DateRange($this->dateExactMock('DATE1'), null)));
        $this->assertEquals('BEF DATE2', (string)(new DateRange(null, $this->dateExactMock('DATE2'))));
        $this->assertEquals('BET DATE1 AND DATE2', (string)(new DateRange($this->dateExactMock('DATE1'), $this->dateExactMock('DATE2'))));
    }

    public function test_DateRange_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Please supply any of AFT/BEF dates (or both) for period.'));
        new DateRange();
    }

    public function fromStringProvider()
    {
        return [
            ['AFT 1922', 1922],
            ['BEF 1991', 1991],
            ['BET 1922 AND 1991', 1922],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function test_DateRange_fromString($value, $year)
    {
        $date = DateRange::fromString($value);
        $this->assertEquals($value, (string)$date);
        $this->assertEquals($year, $date->year());
        $this->assertMatchesRegularExpression(DateRange::REGEXP, $value);
    }

    public function test_DateRange_empty_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Please supply any of AFT/BEF dates (or both) for period.'));
        DateRange::fromString('');
    }

    public function test_DateRange_invalid_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("Unable to decode period date string 'TEST'."));
        DateRange::fromString('TEST');
    }
}
