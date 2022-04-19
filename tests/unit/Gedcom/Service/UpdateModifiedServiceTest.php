<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class UpdateModifiedServiceTest extends TestCase
{
    public function gedcomProvider()
    {
        return [
            'GEDCOM with only HEAD' => [
                <<<GED
0 HEAD
GED
            ],
            'GEDCOM with SOUR' => [
                <<<GED
0 HEAD
1 SOUR test
1 GEDC
2 VERS 5.5.1
0 TRLR
GED
            ],
            'GEDCOM full' => [
                <<<GED
0 HEAD
1 SOUR test
2 VERS 1.2.3
2 NAME TESTER
2 CORP PHPUnit
3 ADDR http://example.com
1 GEDC
2 VERS 5.5.1
0 TRLR
GED
            ],
        ];
    }

    /**
     * @dataProvider gedcomProvider
     */
    public function test_updateGedcomModificationDate($gedcomString)
    {
        $gedcom = Document::createFromGedcom($gedcomString);
        $service = new UpdateModifiedService();
        $service->updateGedcomModificationDate($gedcom);
        $this->assertEquals(UpdateModifiedService::SOUR, $gedcom->xpath('string(/G:GEDCOM/G:HEAD/G:SOUR/@value)'));
        $this->assertEquals('0.0.0-dev', $gedcom->xpath('string(/G:GEDCOM/G:HEAD/G:SOUR/G:VERS/@value)'));
        $this->assertEquals(UpdateModifiedService::NAME, $gedcom->xpath('string(/G:GEDCOM/G:HEAD/G:SOUR/G:NAME/@value)'));
        $this->assertEquals(UpdateModifiedService::CORP, $gedcom->xpath('string(/G:GEDCOM/G:HEAD/G:SOUR/G:CORP/@value)'));
        $this->assertEquals(UpdateModifiedService::ADDR, $gedcom->xpath('string(/G:GEDCOM/G:HEAD/G:SOUR/G:CORP/G:ADDR/@value)'));
    }

    public function test_updateGedcomModificationDate_fails_without_HEAD_tag()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Incorrect GEDCOM. No HEAD tag found.'));
        $gedcomString = <<<GED
0 @I1@ INDI
0 TRLR
GED;

        $gedcom = Document::createFromGedcom($gedcomString);
        $service = new UpdateModifiedService();
        $service->updateGedcomModificationDate($gedcom);
    }

    public function test_updateNodeModificationDate()
    {
        $gedcomString = <<<GED
0 HEAD
1 SOUR test
2 VERS 1.2.3
2 NAME TESTER
2 CORP PHPUnit
3 ADDR http://example.com
1 GEDC
2 VERS 5.5.1
0 @I1@ INDI
1 NAME Person
0 TRLR
GED;

        $gedcom = Document::createFromGedcom($gedcomString);
        $service = new UpdateModifiedService();
        $service->updateNodeModificationDate($gedcom, $gedcom->indiNode('I1'));
        $this->assertCount(1, $gedcom->xpath('/G:GEDCOM/G:INDI[@xref="I1"]/G:CHAN'));
        $this->assertNotEmpty($gedcom->xpath('string(/G:GEDCOM/G:INDI[@xref="I1"]/G:CHAN/G:DATE/@value)'));
        $this->assertNotEmpty($gedcom->xpath('string(/G:GEDCOM/G:INDI[@xref="I1"]/G:CHAN/G:DATE/G:TIME/@value)'));
    }
}
