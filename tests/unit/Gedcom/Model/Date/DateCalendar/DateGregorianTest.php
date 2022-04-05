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
}
