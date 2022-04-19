<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\IndiXrefsRenameService;
use Zebooka\Gedcom\Service\TransliteratorService;

class IndiXrefsRenameServiceTest extends TestCase
{
    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../../res/gedcom.ged'));
    }

    public function test_isComposedXref()
    {
        $service = new IndiXrefsRenameService(new TransliteratorService());

        $this->assertTrue($service->isComposedXref('I1980FAMILYFATHER'));
        $this->assertTrue($service->isComposedXref('I1980FAMILYLOOOOON99'));
        $this->assertTrue($service->isComposedXref('I1980FAMILYSHORT1'));

        $this->assertFalse($service->isComposedXref('I1980FAMILYTOOLOOOOOOOOOOOOONG'));
        $this->assertFalse($service->isComposedXref('I__FAMILYUNCLE'));
        $this->assertFalse($service->isComposedXref('I00001'));
        $this->assertFalse($service->isComposedXref('TEST'));
        $this->assertFalse($service->isComposedXref('F1234AAABBB'));
    }

    public function test_isComposedXref_empty_throws_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Unexpected empty XREF value.'));
        $service = new IndiXrefsRenameService(new TransliteratorService());
        $this->assertFalse($service->isComposedXref(''));
    }

    public function test_isXrefAvailable()
    {
        $service = new IndiXrefsRenameService(new TransliteratorService());
        $gedcom = $this->gedcom();
        $this->assertFalse($service->isXrefAvailable('SON', [], $gedcom));
        $this->assertTrue($service->isXrefAvailable('SON', ['SON' => 'I2000FAMILYSON'], $gedcom)); // xref is available again, once it was set to be renamed.
        $this->assertTrue($service->isXrefAvailable('RANDOM', [], $gedcom));
        $this->assertFalse($service->isXrefAvailable('RANDOM', ['SOME'=> 'RANDOM'], $gedcom));
    }

    public function test_collectXrefsToRename()
    {
        $service = new IndiXrefsRenameService(new TransliteratorService());
        $renameMap = $service->collectXrefsToRename($this->gedcom());
        $this->assertIsArray($renameMap);
        $this->assertCount(8, $renameMap);
        $this->assertEquals('I2000FAMILYSON', $renameMap['SON']);
        $this->assertEquals('I2005FAMILYDAUGHTER', $renameMap['DAUGHTER']);
        $this->assertEquals('I1975FAMILYFATHER', $renameMap['FATHER']);
        $this->assertEquals('I1980FAMILYMOTHER', $renameMap['MOTHER']);
        $this->assertEquals('I1950FAMILYGRAND', $renameMap['GRANDFATHER']);
        $this->assertEquals('I1948FAMILYGRANNY', $renameMap['GRANDMOTHER']);
        $this->assertEquals('I2020FAMILYGRAND', $renameMap['GRANDDAUGHTER']);
        $this->assertEquals('I____FAMILYUNCLE', $renameMap['UNCLE']);
    }

    public function test_increaseXrefSeqence()
    {
        $service = new IndiXrefsRenameService(new TransliteratorService());
        $this->assertEquals('I1234TEST4', $service->increaseXrefSequence('I1234TEST3'));
        $this->assertEquals('I8888VERYVERYVERYLO3', $service->increaseXrefSequence('I8888VERYVERYVERYLONGIDENTIFIER2'));
        $this->assertEquals('I8888VERYVERYVERYLO2', $service->increaseXrefSequence('I8888VERYVERYVERYLONGIDENTIFIER'));
    }

    public function test_increaseXrefSeqence_fails_on_incorrect_xref()
    {
        $this->expectExceptionObject(new \UnexpectedValueException("XREF 'TEST' does not match regular expression."));
        $service = new IndiXrefsRenameService(new TransliteratorService());
        $service->increaseXrefSequence('TEST');
    }
}
