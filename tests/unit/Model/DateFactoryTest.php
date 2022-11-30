<?php

namespace Zebooka\Gedcom\Model;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateGregorian;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateHebrew;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateJulian;
use Zebooka\Gedcom\Model\Date\DateInt;
use Zebooka\Gedcom\Model\Date\DatePhrase;
use Zebooka\Gedcom\Model\DateFactory;

class DateFactoryTest extends TestCase
{
    public function fromStringProvider()
    {
        return [
            ['1985', DateGregorian::class],
            ['1 BCE', DateGregorian::class],
            ['MAY 1985', DateGregorian::class],
            ['28 MAY 1985', DateGregorian::class],
            ['GREGORIAN 28 MAY 1985', DateGregorian::class],
            ['@#DGREGORIAN@ 28 MAY 1985', DateGregorian::class],
            ['@#DGREGORIAN@ 28 MAY 1985B.C.', DateGregorian::class],
            ['JULIAN 27 BCE', DateJulian::class],
            ['@#DJULIAN@ 27B.C.', DateJulian::class],
            ['JULIAN 15 MAY 1985', DateJulian::class],
            ['JULIAN 15 MAY 27 BCE', DateJulian::class],
            ['HEBREW 8 SVN 5745', DateHebrew::class],
            ['@#DHEBREW@ 1 TSH 1', DateHebrew::class],
//            ['FRENCH_R 8 FLOR 8', DateCalendar::class],
            ['INT 28 MAY 1985 (after 27 of May 1985)', DateInt::class],
            ['(after 27 of May 1985)', DatePhrase::class],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function test_DateFactory_fromString($value, $class)
    {
        $date = DateFactory::fromString($value);
        $this->assertEquals($value, (string)$date);
        $this->assertEquals($class, get_class($date));
    }
}
