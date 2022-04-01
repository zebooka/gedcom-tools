<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateExact;

class DateExactTest extends TestCase
{
    public function test_DateExact_toString()
    {
        $this->assertEquals('1985', (string)(new DateExact(1985)));
        $this->assertEquals('MAY 1985', (string)(new DateExact(1985, 'MAY')));
        $this->assertEquals('28 MAY 1985', (string)(new DateExact(1985, 'MAY', 28)));
        $this->assertEquals('GREGORIAN 28 MAY 1985', (string)(new DateExact(1985, 'MAY', 28, 'GREGORIAN')));
        $this->assertEquals('JULIAN 27 BCE', (string)(new DateExact(27, null, null, 'JULIAN', 'BCE')));
    }

    public function fromStringProvider()
    {
        return [
            ['1985'],
            ['MAY 1985'],
            ['28 MAY 1985'],
            ['GREGORIAN 28 MAY 1985'],
            ['JULIAN 27 BCE'],
            ['JULIAN 15 MAY 1985'],
            ['JULIAN 15 MAY 27 BCE'],
//            ['HEBREW 8 SVN 5745'], // TODO
//            ['FRENCH_R 8 FLOR 8'],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function test_DateExact_fromString($value)
    {
        $this->assertEquals($value, (string)DateExact::fromString($value));
        $this->assertMatchesRegularExpression(DateExact::REGEXP, $value);
    }
}
