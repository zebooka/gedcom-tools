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
}
