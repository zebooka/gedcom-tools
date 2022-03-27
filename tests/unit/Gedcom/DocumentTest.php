<?php

namespace Test\Zebooka\Gedcom;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Parser;

class DocumentTest extends TestCase
{
    public function test_documentFromGedcom()
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
        $doc = Document::createFromGedcom($string);
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertInstanceOf(\DOMDocument::class, $doc->dom());
        $this->assertInstanceOf(\SimpleXMLElement::class, $doc->sxml());
        $this->assertEquals('1.2.3', $doc->version());
    }
}
