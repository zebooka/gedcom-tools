<?php

namespace Zebooka\Gedcom;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Parser;

class DocumentTest extends TestCase
{
    public function test_documentFromGedcom()
    {
        $string = <<<GEDCOM
0 HEAD
1 GEDC
2 VERS 1.2.3
1 DATE @#DJULIAN@ 27 JAN 2019
1 SUBM @SUBMITTER1@
0 @SUBMITTER1@ SUBM
1 NAME Submitter Name
0 TRLR
GEDCOM;
        $doc = Document::createFromGedcom($string);
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertInstanceOf(\DOMDocument::class, $doc->dom());
        $this->assertInstanceOf(\SimpleXMLElement::class, $doc->sxml());
        $this->assertEquals('1.2.3', $doc->version());
    }

    /**
     * @see https://en.wikipedia.org/wiki/Whitespace_character
     * @see https://www.compart.com/en/unicode/category/Zs
     */
    public function test_space_characters()
    {
        $tab = "\t\t\t";
        $spaces = html_entity_decode('&#9;&#32;&#160;&#5760;&#8192;&#8193;&#8194;&#8195;&#8196;&#8197;&#8198;&#8199;&#8200;&#8201;&#8202;&#8239;&#8287;&#12288;');
        $lineBreaks = html_entity_decode('&#11;&#12;&#133;&#8232;&#8233;');
        $special = html_entity_decode('&#6158;&#8203;&#8204;&#8205;&#8288');


        $tabsAndLineSep = html_entity_decode('&#9;&#11;&#8232;&#8233;');
        $string = <<<GEDCOM
0 HEAD
1 GEDC
2 VERS 1.2.3
2 NOTE This is a long line 
3 CONC with     regular spaces and other '$spaces' spaces
3 CONC and {$lineBreaks} line breaks
3 CONC and other special '{$special}' symbols
0 TRLR

GEDCOM;
        $doc = Document::createFromGedcom($string);
        $x = Parser::parseString($string);
        $s = new \DOMDocument();
        $s->loadXML($x);
        $this->assertEquals($string, "{$doc}");
    }
}
