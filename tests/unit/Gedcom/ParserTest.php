<?php

namespace Test\Zebooka\Gedcom;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Parser;

class ParserTest extends TestCase
{
    public function test_createElementFromLine() {
        $stack = [];
        $this->assertEquals('<HEAD>', Parser::createElementFromLine('0 HEAD',$stack));
        $this->assertEquals(['HEAD'], $stack);
        $this->assertEquals('<SOUR value="TEST">', Parser::createElementFromLine('1 SOUR TEST',$stack));
        $this->assertEquals(['HEAD', 'SOUR'], $stack);
        $this->assertEquals('', Parser::createElementFromLine("\r\n\t   \n",$stack));
        $this->assertEquals(['HEAD', 'SOUR'], $stack);
        $this->assertEquals('<VERS value="1.2.3">', Parser::createElementFromLine('2 VERS 1.2.3',$stack));
        $this->assertEquals(['HEAD', 'SOUR', 'VERS'], $stack);
        $this->assertEquals('', Parser::createElementFromLine('',$stack));
        $this->assertEquals(['HEAD', 'SOUR', 'VERS'], $stack);
        $this->assertEquals('</VERS></SOUR><DATE escape="DJULIAN" value="27 JAN 2019">', Parser::createElementFromLine('1 DATE @#DJULIAN@ 27 JAN 2019',$stack));
        $this->assertEquals('</DATE><SUBM xref="SUBMITTER1">', Parser::createElementFromLine('1 SUBM @SUBMITTER1@',$stack));
        $this->assertEquals(['HEAD', 'SUBM'], $stack);
        $this->assertEquals('</SUBM></HEAD>', Parser::closeTags($stack, 0));
        $this->assertEquals([], $stack);
        $this->assertEquals('<SUBM id="SUBMITTER1">', Parser::createElementFromLine('0 @SUBMITTER1@ SUBM',$stack));
        $this->assertEquals('<NAME value="Submitter Name">', Parser::createElementFromLine('1 NAME Submitter Name',$stack));
        $this->assertEquals('</NAME></SUBM>', Parser::closeTags($stack, 0));
        $this->assertEquals([], $stack);
        $this->assertEquals('<TRLR>', Parser::createElementFromLine('0 TRLR',$stack));
        $this->assertEquals(['TRLR'], $stack);
    }

    public function test_parseString()
    {
        $string = <<<GEDCOM
0 HEAD
1 SOUR TEST
2 VERS 1.2.3
1 DATE @#DJULIAN@ 27 JAN 2019
1 SUBM @SUBMITTER1@
0 @SUBMITTER1@ SUBM
1 NAME Submitter Name
0 TRLR
GEDCOM;
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GEDCOM xmlns="https://zebooka.com/gedcom/">
    <HEAD>
        <SOUR value="TEST">
            <VERS value="1.2.3"/>
        </SOUR>
        <DATE escape="DJULIAN" value="27 JAN 2019"/>
        <SUBM xref="SUBMITTER1"/>
    </HEAD>
    <SUBM id="SUBMITTER1">
        <NAME value="Submitter Name"/>
    </SUBM>
    <TRLR/>
</GEDCOM>
XML;

        $this->assertXmlStringEqualsXmlString($xml, Parser::parseString($string));
    }
}
