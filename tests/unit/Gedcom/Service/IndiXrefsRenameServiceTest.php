<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\IndiXrefsRenameService;
use Zebooka\Gedcom\Service\TransliteratorService;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class IndiXrefsRenameServiceTest extends TestCase
{
    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../../res/gedcom.ged'));
    }

    private function service()
    {
        return new IndiXrefsRenameService(new TransliteratorService(), new UpdateModifiedService());
    }

    public function test_isComposedXref()
    {
        $gedcom = $this->gedcom();
        $service = $this->service();

        $this->assertTrue($service->isComposedXref('I1980FAMILYFATHER', $gedcom));
        $this->assertTrue($service->isComposedXref('I1980FAMILYLOOOOON99', $gedcom));
        $this->assertTrue($service->isComposedXref('I1980FAMILYSHORT1', $gedcom));
        $this->assertTrue($service->isComposedXref('A1980FAMILYSHORT1', $gedcom));

        $this->assertFalse($service->isComposedXref('J1980FAMILYSHORT1', $gedcom));
        $this->assertFalse($service->isComposedXref('I1980FAMILYTOOLOOOOOOOOOOOOONG', $gedcom));
        $this->assertFalse($service->isComposedXref('I__FAMILYUNCLE', $gedcom));
        $this->assertFalse($service->isComposedXref('I00001', $gedcom));
        $this->assertFalse($service->isComposedXref('TEST', $gedcom));
        $this->assertFalse($service->isComposedXref('F1234AAABBB', $gedcom));
    }

    public function test_isComposedXref_long_gedcom_7x()
    {
        $gedcom = $this->gedcom();
        $gedcom->xpath('/G:GEDCOM/G:HEAD/G:GEDC/G:VERS')->item(0)->setAttribute('value', '7.0.0');
        $this->assertTrue($gedcom->isVersion7x());
        $service = $this->service();

        $this->assertTrue($service->isComposedXref('I1980FAMILYTOOLOOOOOOOOOOOOONG', $gedcom));
    }

    public function test_isComposedXref_empty_throws_exception()
    {
        $gedcom = $this->gedcom();
        $this->expectExceptionObject(new \UnexpectedValueException('Unexpected empty XREF value.'));
        $service = $this->service();
        $this->assertFalse($service->isComposedXref('', $gedcom));
    }

    public function test_isXrefAvailable()
    {
        $service = $this->service();
        $gedcom = $this->gedcom();
        $this->assertFalse($service->isXrefAvailable('SON', [], $gedcom));
        $this->assertTrue($service->isXrefAvailable('SON', ['SON' => 'I2000FAMILYSONNAME'], $gedcom)); // xref is available again, once it was set to be renamed.
        $this->assertTrue($service->isXrefAvailable('RANDOM', [], $gedcom));
        $this->assertFalse($service->isXrefAvailable('RANDOM', ['SOME' => 'RANDOM'], $gedcom));
    }

    public function test_collectXrefsToRename()
    {
        $service = $this->service();
        $renameMap = $service->collectXrefsToRename($this->gedcom());
        $this->assertIsArray($renameMap);
        $this->assertCount(8, $renameMap);
        $this->assertEquals('I2000FAMILYSONNAME', $renameMap['SON']);
        $this->assertEquals('I2005FAMILYDAUGHTERN', $renameMap['DAUGHTER']);
        $this->assertEquals('I1975FAMILYFATHERDAD', $renameMap['FATHER']);
        $this->assertEquals('I1980FAMILYMOTHER', $renameMap['MOTHER']);
        $this->assertEquals('I1950FAMILYGRANDPA', $renameMap['GRANDFATHER']);
        $this->assertEquals('I1948FAMILYGRANNY', $renameMap['GRANDMOTHER']);
        $this->assertEquals('I2020FAMILYGRANDDAUG', $renameMap['GRANDDAUGHTER']);
        $this->assertEquals('A____FAMILYUNCLE', $renameMap['UNCLE']);
    }

    public function test_increaseXrefSeqence()
    {
        $service = $this->service();
        $this->assertEquals('I1234TEST4', $service->increaseXrefSequence('I1234TEST3'));
        $this->assertEquals('I8888VERYVERYVERYLO3', $service->increaseXrefSequence('I8888VERYVERYVERYLONGIDENTIFIER2'));
        $this->assertEquals('I8888VERYVERYVERYLO2', $service->increaseXrefSequence('I8888VERYVERYVERYLONGIDENTIFIER'));
    }

    public function test_increaseXrefSeqence_fails_on_incorrect_xref()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("XREF 'TEST' does not match regular expression."));
        $service = $this->service();
        $service->increaseXrefSequence('TEST');
    }

    public function test_renameXrefs()
    {
        $gedcom = $this->gedcom();
        $service = $this->service();
        $service->renameXrefs($gedcom);
        foreach ($gedcom->xpath('/G:GEDCOM/G:HEAD/G:SOUR | /G:GEDCOM/*/G:CHAN') as $node)
        {
            /** @var \DOMElement $node */
            $node->remove();
        }
        $this->assertEquals(file_get_contents(__DIR__ . '/../../../res/gedcom_xrefs_indi.ged'), "{$gedcom}");
    }
}
