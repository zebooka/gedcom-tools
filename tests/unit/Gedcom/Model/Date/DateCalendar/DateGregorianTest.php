<?php

namespace Test\Zebooka\Gedcom\Model\Date\DateCalendar;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateGregorian;

class DateGregorianTest extends TestCase
{
    public function test_0_year_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect year supplied.'));
        DateGregorian::fromString('0');
    }

    public function test_0_year_construct_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect year supplied.'));
        new DateGregorian(0);
    }

    public function toTimestampProvider()
    {
        return [
            ['1985-05-28', '28 MAY 1985', 1985],
            ['1985-05-01', 'MAY 1985', 1985],
            ['1985-01-01', '1985', 1985],
            ['1242-01-01', '1242', 1242],
            ['0001-01-01', '1', 1],
            ['0000-01-01', '1 BCE', 1],
            ['-0001-01-01', '2 BCE', 2],
        ];
    }

    /**
     * @dataProvider toTimestampProvider
     */
    public function test_toTimestamp($formattedGregorian, $gregorian, $year)
    {
        $date = DateGregorian::fromString($gregorian);
        $this->assertEquals($formattedGregorian, date('Y-m-d', $date->toTimestamp()));
        $this->assertEquals($year, $date->year());
    }
}
