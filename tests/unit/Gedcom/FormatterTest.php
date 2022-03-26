<?php

namespace Test\Zebooka\Gedcom;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Formatter;

class FormatterTest extends TestCase
{
    private function gedcom()
    {
        return <<<GEDCOM
0 HEAD
1 SOUR TEST
2 VERS 1.2.3
1 DATE @#DJULIAN@ 27 JAN 2019
1 SUBM @SUBMITTER1@
0 @SUBMITTER1@ SUBM
1 NAME Submitter Name
0 TRLR

GEDCOM;
    }

    private function xml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GEDCOM xmlns="https://zebooka.com/gedcom/5.5.1">
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
    }

    public function test_formatXML()
    {
        $this->assertEquals(
            str_replace("\r", '', $this->gedcom()),
            str_replace("\r", '', Formatter::formatXML($this->xml()))
        );
    }

    public function test_formatDOMDocument()
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->xml());
        $this->assertEquals(
            str_replace("\r", '', $this->gedcom()),
            str_replace("\r", '', Formatter::formatDOMDocument($dom))
        );
    }

    public function test_formatSimpleXMLElement()
    {
        $this->assertEquals(
            str_replace("\r", '', $this->gedcom()),
            str_replace("\r", '', Formatter::formatSimpleXMLElement(new \SimpleXMLElement($this->xml())))
        );
    }
}
