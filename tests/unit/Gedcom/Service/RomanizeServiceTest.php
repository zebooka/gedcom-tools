<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\RomanizeService;
use Zebooka\Gedcom\Service\TransliteratorService;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class RomanizeServiceTest extends TestCase
{
    use FixGedcomModifiedDatesTrait;

    private function service($romanizedType = null)
    {
        return new RomanizeService(
            new TransliteratorService(TransliteratorService::CYRILLIC, $romanizedType),
            new UpdateModifiedService()
        );
    }

    public function test_fixSpaceAroundFamilyName()
    {
        $gedcomString = <<<GED
0 HEAD
1 SOUR
2 NAME Program
0 @I1@ INDI
1 NAME First Second/FAMILY/
2 GIVN First, Second
2 SURN FAMILY
0 TRLR

GED;

        $gedcom = Document::createFromGedcom($gedcomString);
        $service = $this->service();
        $service->fixSpaceAroundFamilyName($gedcom);
        $this->fixGedcomModifiedDate($gedcom);

        $processedGedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME First Second /FAMILY/
2 GIVN First, Second
2 SURN FAMILY
0 TRLR

GED;
        $this->assertEquals($processedGedcomString, "{$gedcom}");
    }

    public function test_romanizeNames()
    {
        $gedcomString = <<<GED
0 HEAD
1 SOUR
2 NAME Program
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
0 TRLR

GED;

        $gedcom = Document::createFromGedcom($gedcomString);

        $service = $this->service();
        $service->romanizeNames($gedcom);
        $this->fixGedcomModifiedDate($gedcom);

        $processedGedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
2 ROMN Konstantin Eduardovich /TSIOLKOVSKIY/
3 GIVN Konstantin, Eduardovich
3 SURN TSIOLKOVSKIY
0 TRLR

GED;
        $this->assertEquals($processedGedcomString, "{$gedcom}");
    }

    public function test_romanizeNames_typed()
    {
        $gedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
0 TRLR

GED;

        $gedcom = Document::createFromGedcom($gedcomString);

        $service = $this->service('cyrillic');
        $service->romanizeNames($gedcom);
        $this->fixGedcomModifiedDate($gedcom);

        $processedGedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
2 ROMN Konstantin Eduardovich /TSIOLKOVSKIY/
3 GIVN Konstantin, Eduardovich
3 SURN TSIOLKOVSKIY
3 TYPE cyrillic
0 TRLR

GED;
        $this->assertEquals($processedGedcomString, "{$gedcom}");
    }

    public function test_romanizeNames_no_overwrite_for_existing()
    {
        $gedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
2 ROMN Konstantin Eduardovic /TSIOLKOVSKII/
3 GIVN Konstantin, Eduardovic
3 SURN TSIOLKOVSKII
3 TYPE gost
0 TRLR

GED;

        $gedcom = Document::createFromGedcom($gedcomString);

        $service = $this->service();
        $service->romanizeNames($gedcom);
        $this->fixGedcomModifiedDate($gedcom);
        $this->assertEquals($gedcomString, "{$gedcom}");
    }

    public function test_romanizeNames_overwrite()
    {
        $gedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
2 ROMN Konstantin Eduardovic /TSIOLKOVSKII/
3 GIVN Konstantin, Eduardovic
3 SURN TSIOLKOVSKII
3 TYPE gost
0 TRLR

GED;

        $gedcom = Document::createFromGedcom($gedcomString);

        $service = $this->service();
        $service->romanizeNames($gedcom, true);
        $this->fixGedcomModifiedDate($gedcom);

        $processedGedcomString = <<<GED
0 HEAD
0 @I1@ INDI
1 NAME Константин Эдуардович /ЦИОЛКОВСКИЙ/
2 GIVN Константин, Эдуардович
2 SURN ЦИОЛКОВСКИЙ
2 ROMN Konstantin Eduardovich /TSIOLKOVSKIY/
3 GIVN Konstantin, Eduardovich
3 SURN TSIOLKOVSKIY
0 TRLR

GED;
        $this->assertEquals($processedGedcomString, "{$gedcom}");
    }
}
