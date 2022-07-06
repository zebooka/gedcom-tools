<?php

namespace Test\Zebooka\Gedcom;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Formatter;

class FormatterTest extends TestCase
{
    private function gedcom($bom = true)
    {
        return ($bom ? "\xEF\xBB\xBF" : '') .
            <<<GEDCOM
0 HEAD
1 SOUR TEST
2 VERS 5.5.1
1 DATE @#DJULIAN@ 27 JAN 2019
1 SUBM @SUBMITTER1@
0 @SUBMITTER1@ SUBM
1 NAME Submitter Name
0 TRLR

GEDCOM;
    }

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
        <DATE escape="DJULIAN" value="27 JAN 2019"/>
        <SUBM pointer="SUBMITTER1"/>
    </HEAD>
    <SUBM xref="SUBMITTER1">
        <NAME value="Submitter Name"/>
    </SUBM>
    <TRLR/>
</GEDCOM>

XML;
    }

    private function xmlWithNoise($bom = true)
    {
        $bom = intval($bom);
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<g:GEDCOM xmlns:g="https://zebooka.com/gedcom/" xmlns:noise="https://example.com/noise" g:bom="{$bom}">
    <noise:test>123</noise:test>
    <g:HEAD noise:attrib="value" noise:value="hello world">
        <g:SOUR value="TEST">
            <g:VERS value="5.5.1"/>
        </g:SOUR>
        <noise:another>456</noise:another>
        <g:DATE escape="DJULIAN" value="27 JAN 2019"/>
        <g:SUBM pointer="SUBMITTER1"/>
    </g:HEAD>
    <g:SUBM xref="SUBMITTER1">
        <g:NAME value="Submitter Name"/>
    </g:SUBM>
    <g:TRLR/>
</g:GEDCOM>

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

    public function test_formatDOMDocument_with_bom()
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

    public function test_additionalNamespacesAreFiltered()
    {
        $this->assertEquals(
            str_replace("\r", '', $this->gedcom()),
            str_replace("\r", '', Formatter::formatXML($this->xmlWithNoise()))
        );
    }

    public function test_additionalNamespacesAreFilteredFromDOMDocument()
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->xmlWithNoise());
        $this->assertEquals(
            str_replace("\r", '', $this->gedcom()),
            str_replace("\r", '', Formatter::formatDOMDocument($dom))
        );
    }

    public function test_additionalNamespacesAreFilteredFromSimpleXMLElement()
    {
        $this->assertEquals(
            str_replace("\r", '', $this->gedcom()),
            str_replace("\r", '', Formatter::formatSimpleXMLElement(new \SimpleXMLElement($this->xmlWithNoise())))
        );
    }

    public function test_bom_stays()
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->xml(true));
        $this->assertStringStartsWith("\xEF\xBB\xBF", Formatter::formatDOMDocument($dom));
    }

    public function test_bom_absent()
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->xml(false));
        $this->assertStringStartsNotWith("\xEF\xBB\xBF", Formatter::formatDOMDocument($dom));
    }
}
