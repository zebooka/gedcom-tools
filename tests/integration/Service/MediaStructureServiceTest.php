<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\IndiMedia;
use Zebooka\Gedcom\Service\MediaStructureService;

class MediaStructureServiceTest extends TestCase
{
    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../res/gedcom_media.ged'));
    }

    private function service(Document $gedcom = null)
    {
        return new MediaStructureService($gedcom ?: $this->gedcom());
    }

    public function test_readStructure()
    {
        $gedcom = $this->gedcom();
        $service = $this->service($gedcom);

        $list = $service->readStructure(new \SplFileInfo(__DIR__ . '/../../res/media-service'));
        $this->assertIsArray($list);
        $this->assertCount(1, $list);
        $this->assertEquals('2000 FAMILY Son Name', IndiMedia::composeDirectoryName($list['SON']->indi()));
    }

    public function test_readGedcom()
    {
        $gedcom = $this->gedcom();
        $service = $this->service($gedcom);
        $list = $service->readGedcom();
        $this->assertIsArray($list);
        $listOfDirs = [
            '2000 FAMILY Son Name',
            '2005 FAMILY Daughter Name',
            '1975 FAMILY Father Daddy',
            '1980~ FAMILY Mother',
            '1950~YYYY~ FAMILY Grand Pa',
            '1948~2020~ FAMILY Granny',
            '2020 FAMILY Grand Daughter',
            'FAMILY Uncle',
            '1900-2000 ANOTHER Old',
            'FAMILY Uncle (UNCLE2)',
        ];
        $unique = [];
        $this->assertCount(count($listOfDirs), $list);
        foreach ($list as $item) {
            $this->assertInstanceOf(IndiMedia::class, $item);
            $this->assertContains($item->directory()->getBasename(), $listOfDirs);
            $this->assertNotContains($item->directory()->getPathname(), $unique);
            $unique[] = $item->directory()->getPathname();
        }
    }

    public function test_indis_with_same_names_generate_different_dirs()
    {
        $this->markTestSkipped('TODO');
    }
}
