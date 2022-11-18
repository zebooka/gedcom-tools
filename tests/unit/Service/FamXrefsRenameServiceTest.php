<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\FamXrefsRenameService;
use Zebooka\Gedcom\Service\TransliteratorService;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class FamXrefsRenameServiceTest extends TestCase
{
    use FixGedcomModifiedDatesTrait;

    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../res/gedcom.ged'));
    }

    private function service()
    {
        return new FamXrefsRenameService(new TransliteratorService(), new UpdateModifiedService());
    }

    public function test_isComposedXref()
    {
        $gedcom = $this->gedcom();
        $service = $this->service();

        $this->assertTrue($service->isComposedXref('F____FAMILY', $gedcom));
        $this->assertTrue($service->isComposedXref('F1980FAMILYLOOOOON99', $gedcom));
        $this->assertTrue($service->isComposedXref('F1980FAMILYSHORT1', $gedcom));

        $this->assertFalse($service->isComposedXref('F____', $gedcom));
        $this->assertFalse($service->isComposedXref('I1980FAMILYSHORT1', $gedcom));
        $this->assertFalse($service->isComposedXref('F1980FAMILYTOOLOOOOOOOOOOOOONG', $gedcom));
        $this->assertFalse($service->isComposedXref('F__FAMILYUNCLE', $gedcom));
        $this->assertFalse($service->isComposedXref('F00001', $gedcom));
        $this->assertFalse($service->isComposedXref('TEST', $gedcom));
        $this->assertFalse($service->isComposedXref('I1234AAABBB', $gedcom));
    }

    public function test_isComposedXref_long_gedcom_7x()
    {
        $gedcom = $this->gedcom();
        $gedcom->xpath('/G:GEDCOM/G:HEAD/G:GEDC/G:VERS')->item(0)->setAttribute('value', '7.0.0');
        $this->assertTrue($gedcom->isVersion7x());
        $service = $this->service();

        $this->assertTrue($service->isComposedXref('F1980FAMILYTOOLOOOOOOOOOOOOONG', $gedcom));
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
        $this->assertFalse($service->isXrefAvailable('FAM', [], $gedcom));
        $this->assertTrue($service->isXrefAvailable('FAM', ['FAM' => 'F1970FAMILY'], $gedcom)); // xref is available again, once it was set to be renamed.
        $this->assertTrue($service->isXrefAvailable('RANDOM', [], $gedcom));
        $this->assertFalse($service->isXrefAvailable('RANDOM', ['SOME'=> 'RANDOM'], $gedcom));
    }

    public function test_collectXrefsToRename()
    {
        $service = $this->service();
        $renameMap = $service->collectXrefsToRename($this->gedcom());
        $this->assertIsArray($renameMap);
        $this->assertCount(2, $renameMap);
        $this->assertEquals('F1999FAMILY', $renameMap['FAM']);
        $this->assertEquals('F____FAMILY', $renameMap['DFAM']);

        $renameMap = $service->collectXrefsToRename($this->gedcom(), [], true);
        $this->assertIsArray($renameMap);
        $this->assertCount(3, $renameMap);
        $this->assertEquals('F1999FAMILY', $renameMap['FAM']);
        $this->assertEquals('F1970FAMILY', $renameMap['F1970ANOTHER2']);
        $this->assertEquals('F____FAMILY', $renameMap['DFAM']);
    }

    public function test_increaseXrefSeqence()
    {
        $service = $this->service();
        $this->assertEquals('F1234TEST4', $service->increaseXrefSequence('F1234TEST3'));
        $this->assertEquals('F8888VERYVERYVERYLO3', $service->increaseXrefSequence('F8888VERYVERYVERYLONGIDENTIFIER2'));
        $this->assertEquals('F8888VERYVERYVERYLO2', $service->increaseXrefSequence('F8888VERYVERYVERYLONGIDENTIFIER'));
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
        $this->fixGedcomModifiedDate($gedcom);
        $this->assertEquals(file_get_contents(__DIR__ . '/../../res/gedcom_xrefs_fam.ged'), "{$gedcom}");
    }

    public function test_renameXrefs_force()
    {
        $gedcom = $this->gedcom();
        $service = $this->service();
        $service->renameXrefs($gedcom, [], true);
        $this->fixGedcomModifiedDate($gedcom);
        $this->assertEquals(file_get_contents(__DIR__ . '/../../res/gedcom_xrefs_fam_force.ged'), "{$gedcom}");
    }
}
