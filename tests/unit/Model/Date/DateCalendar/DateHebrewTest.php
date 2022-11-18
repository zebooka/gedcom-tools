<?php

namespace Test\Zebooka\Gedcom\Model\Date\DateCalendar;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateHebrew;

class DateHebrewTest extends TestCase
{
    public function test_0_year_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect year supplied. Only positive years are supported.'));
        DateHebrew::fromString('HEBREW 0');
    }

    public function test_0_year_construct_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect year supplied. Only positive years are supported.'));
        new DateHebrew(0);
    }

    public function test_greorian_date_fromString_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Unable to decode hebrew calendar date string \'1 JAN 1970\'.'));
        DateHebrew::fromString('1 JAN 1970');
    }
}
