<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\DatePeriod;

class DatePeriodTest extends TestCase
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

    public function test_DatePeriod_toString()
    {
        $this->assertEquals('FROM DATE1', (string)(new DatePeriod($this->dateExactMock('DATE1'), null)));
        $this->assertEquals('TO DATE2', (string)(new DatePeriod(null, $this->dateExactMock('DATE2'))));
        $this->assertEquals('FROM DATE1 TO DATE2', (string)(new DatePeriod($this->dateExactMock('DATE1'), $this->dateExactMock('DATE2'))));
    }

    public function test_DatePeriod_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Please supply any of FROM/TO dates (or both) for period.'));
        new DatePeriod();
    }

    public function fromStringProvider()
    {
        return [
            ['FROM 1922'],
            ['TO 1991'],
            ['FROM 1922 TO 1991'],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function test_DatePeriod_fromString($value)
    {
        $this->assertEquals($value, (string)DatePeriod::fromString($value));
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
