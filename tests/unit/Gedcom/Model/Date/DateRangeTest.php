<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateExact;
use Zebooka\Gedcom\Model\Date\DateRange;

class DateRangeTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|null|DateExact
     */
    private function dateExactMock($string)
    {
        return \Mockery::mock(DateExact::class)
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
            ['AFT 1922'],
            ['BEF 1991'],
            ['BET 1922 AND 1991'],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function test_DateRange_fromString($value)
    {
        $this->assertEquals($value, (string)DateRange::fromString($value));
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
