<?php

namespace Test\Zebooka\Gedcom;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Parser;

class ParserTest extends TestCase
{
    private function xml($bom = true)
    {
        $bom = intval($bom);
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GEDCOM xmlns="https://zebooka.com/gedcom/" bom="{$bom}">
    <HEAD>
        <SOUR value="TEST">
            <VERS value="5.5.1"/>
        </SOUR>
        <DATE value="ABT @#DJULIAN@ 27 JAN 2019"/>
        <SUBM pointer="SUBMITTER1"/>
    </HEAD>
    <SUBM xref="SUBMITTER1">
        <NAME value="Submitter Name"/>
    </SUBM>
    <TRLR/>
</GEDCOM>
XML;
    }

    private function gedcom($bom = true)
    {
        return ($bom ? "\xEF\xBB\xBF" : '') .
            <<<GEDCOM
0 HEAD
1 SOUR TEST
2 VERS 5.5.1
1 DATE ABT @#DJULIAN@ 27 JAN 2019
1 SUBM @SUBMITTER1@
0 @SUBMITTER1@ SUBM
1 NAME Submitter Name
0 TRLR
GEDCOM;
    }

    public function test_createElementFromLine()
    {
        $stack = [];
        $this->assertEquals('<HEAD>', Parser::createElementFromLine('0 HEAD', $stack));
        $this->assertEquals(['HEAD'], $stack);
        $this->assertEquals('<SOUR value="TEST">', Parser::createElementFromLine('1 SOUR TEST', $stack));
        $this->assertEquals(['HEAD', 'SOUR'], $stack);
        $this->assertEquals('', Parser::createElementFromLine("\r\n\t   \n", $stack));
        $this->assertEquals(['HEAD', 'SOUR'], $stack);
        $this->assertEquals('<VERS value="5.5.1">', Parser::createElementFromLine('2 VERS 5.5.1', $stack));
        $this->assertEquals(['HEAD', 'SOUR', 'VERS'], $stack);
        $this->assertEquals('', Parser::createElementFromLine('', $stack));
        $this->assertEquals(['HEAD', 'SOUR', 'VERS'], $stack);
        $this->assertEquals('</VERS></SOUR><DATE value="ABT @#DJULIAN@ 27 JAN 2019">', Parser::createElementFromLine('1 DATE ABT @#DJULIAN@ 27 JAN 2019', $stack));
        $this->assertEquals('</DATE><SUBM pointer="SUBMITTER1">', Parser::createElementFromLine('1 SUBM @SUBMITTER1@', $stack));
        $this->assertEquals(['HEAD', 'SUBM'], $stack);
        $this->assertEquals('</SUBM></HEAD>', Parser::closeTags($stack, 0));
        $this->assertEquals([], $stack);
        $this->assertEquals('<SUBM xref="SUBMITTER1">', Parser::createElementFromLine('0 @SUBMITTER1@ SUBM', $stack));
        $this->assertEquals('<NAME value="Submitter Name">', Parser::createElementFromLine('1 NAME Submitter Name', $stack));
        $this->assertEquals('</NAME></SUBM>', Parser::closeTags($stack, 0));
        $this->assertEquals([], $stack);
        $this->assertEquals('<TRLR>', Parser::createElementFromLine('0 TRLR', $stack));
        $this->assertEquals(['TRLR'], $stack);
    }

    public function test_parseString()
    {
        $this->assertXmlStringEqualsXmlString($this->xml(true), Parser::parseString($this->gedcom(true)));
        $this->assertXmlStringEqualsXmlString($this->xml(false), Parser::parseString($this->gedcom(false)));
    }

    public function test_parseString_fails_on_empty()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Empty GEDCOM file.'));
        Parser::parseString(' ');
    }

    public function test_bom_stays()
    {
        $dom = new \DOMDocument();
        $dom->loadXML(Parser::parseString($this->gedcom(true)));
        $this->assertEquals(1, $dom->documentElement->getAttribute('bom'));
    }

    public function test_bom_absent()
    {
        $dom = new \DOMDocument();
        $dom->loadXML(Parser::parseString($this->gedcom(false)));
        $this->assertEquals(0, $dom->documentElement->getAttribute('bom'));
    }
}
