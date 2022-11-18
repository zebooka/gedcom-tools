<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\DatePeriod;
use Zebooka\Gedcom\Model\Date\YearInterface;

class DatePeriodTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|null|DateCalendar
     */
    private function dateExactMock(string $string, int $year)
    {
        return \Mockery::mock(DateCalendarInterface::class, YearInterface::class)
            ->shouldReceive('__toString')
            ->andReturn($string)
            ->getMock()
            ->shouldReceive('year')
            ->andReturn($year)
            ->getMock();
    }

    public function test_DatePeriod_toString()
    {
        $this->assertEquals('FROM DATE1', (string)(new DatePeriod($this->dateExactMock('DATE1', 1111), null)));
        $this->assertEquals('TO DATE2', (string)(new DatePeriod(null, $this->dateExactMock('DATE2', 2222))));
        $this->assertEquals('FROM DATE1 TO DATE2', (string)(new DatePeriod($this->dateExactMock('DATE1', 1111), $this->dateExactMock('DATE2', 2222))));
    }

    public function test_DatePeriod_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Please supply any of FROM/TO dates (or both) for period.'));
        new DatePeriod();
    }

    public function fromStringProvider()
    {
        return [
            ['FROM 1922', 1922],
            ['TO 1991', 1991],
            ['FROM 1922 TO 1991', 1922],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function test_DatePeriod_fromString($value, $year)
    {
        $date = DatePeriod::fromString($value);
        $this->assertEquals($value, (string)$date);
        $this->assertEquals($year, $date->year());
        $this->assertMatchesRegularExpression(DatePeriod::REGEXP, $value);
    }

    public function test_DatePeriod_empty_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Please supply any of FROM/TO dates (or both) for period.'));
        DatePeriod::fromString('');
    }

    public function test_DatePeriod_invalid_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("Unable to decode period date string 'TEST'."));
        DatePeriod::fromString('TEST');
    }
}
