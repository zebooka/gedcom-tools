<?php

namespace Test\Zebooka\Gedcom\Model\Date\DateCalendar;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateJulian;

class DateJulianTest extends TestCase
{
    public function test_0_year_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect year supplied.'));
        DateJulian::fromString('JULIAN 0');
    }

    public function test_0_year_construct_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect year supplied.'));
        new DateJulian(0);
    }

    public function toTimestampProvider()
    {
        return [
            ['1985-05-28', 'JULIAN 15 MAY 1985'],
            ['1985-05-14', 'JULIAN MAY 1985'],
            // a very special thing about years of Julian calendars. Year is 1984/1985 for 1 JAN, but in gedcom it is written as 1985.
            ['1985-01-14', 'JULIAN 1985'],

            ['1812-09-07', 'JULIAN 26 AUG 1812'],
            ['1582-10-15', 'JULIAN 5 OCT 1582'],
            ['1582-10-14', 'JULIAN 4 OCT 1582'],
            ['1700-03-01', 'JULIAN 19 FEB 1700'],
            ['1800-03-01', 'JULIAN 18 FEB 1800'],
            ['1900-03-01', 'JULIAN 17 FEB 1900'],
            ['2100-03-01', 'JULIAN 16 FEB 2100'],
            ['2200-03-01', 'JULIAN 15 FEB 2200'],
            ['0100-02-27', 'JULIAN 29 FEB 100'],

            ['-0499-02-28', 'JULIAN 5 MAR 500 BCE'], // -499 because php thinks that 0 year exists, while it's not -> 1 BCE, then 1 AD
            ['-0299-02-28', 'JULIAN 4 MAR 300 BCE'],
            ['-0299-03-01', 'JULIAN 5 MAR 300 BCE'],
            ['-0199-03-01', 'JULIAN 4 MAR 200 BCE'],
            ['0000-12-30', 'JULIAN 1'],
            ['-0001-12-30', 'JULIAN 1 BCE'],
            ['0300-02-28', 'JULIAN 28 FEB 300'],
            ['0300-03-01', 'JULIAN 29 FEB 300'],
            ['0500-03-01', 'JULIAN 28 FEB 500'],
            ['0500-03-02', 'JULIAN 29 FEB 500'],

        ];
    }

    /**
     * @dataProvider toTimestampProvider
     */
    public function test_toTimestamp($gregorian, $julian)
    {
        $this->assertEquals($gregorian, date('Y-m-d', DateJulian::fromString($julian)->toTimestamp()));
    }
}
