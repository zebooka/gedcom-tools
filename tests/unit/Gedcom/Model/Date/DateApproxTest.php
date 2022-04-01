<?php

namespace Test\Zebooka\Gedcom\Model\Date;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Model\Date\DateApprox;
use Zebooka\Gedcom\Model\Date\DateExact;

class DateApproxTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|null|DateExact
     */
    private function dateExactMock()
    {
        return \Mockery::mock(DateExact::class)
            ->shouldReceive('__toString')
            ->andReturn('DATE_EXACT')
            ->getMock();
    }

    public function test_DateApprox_toString()
    {
        $this->assertEquals('ABT DATE_EXACT', (string)(new DateApprox('ABT', $this->dateExactMock())));
        $this->assertEquals('CAL DATE_EXACT', (string)(new DateApprox('CAL', $this->dateExactMock())));
        $this->assertEquals('EST DATE_EXACT', (string)(new DateApprox('EST', $this->dateExactMock())));
    }

    public function test_DateApprox_unknown_Lax_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("Only ABT/CAL/EST are allowed for approximate date. 'XXX' was supplied."));
        new DateApprox('XXX', $this->dateExactMock());
    }

    public function test_DateApprox_empty_Lax_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("Only ABT/CAL/EST are allowed for approximate date. '' was supplied."));
        new DateApprox(null, $this->dateExactMock());
    }
}