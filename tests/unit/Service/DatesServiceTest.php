<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\DatesService;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class DatesServiceTest extends TestCase
{
    use FixGedcomModifiedDatesTrait;

    private function gedcom($filename)
    {
        return Document::createFromGedcom(file_get_contents($filename));
    }

    private function service()
    {
        return new DatesService(new UpdateModifiedService());
    }

    public function test_setDeatBuriCremYifDateEmpty()
    {
        $gedcom = $this->gedcom(__DIR__ . '/../../res/dates-service/initial.ged');
        $this->service()->setDeatBuriCremYifDateEmpty($gedcom);
        $this->fixGedcomModifiedDate($gedcom);
        $this->assertEquals(
            "{$this->gedcom(__DIR__ . '/../../res/dates-service/setDeatBuriCremYifDateEmpty.ged')}",
            "{$gedcom}"
        );
    }

    public function test_removeDeatBuriCremYifDateNotEmpty()
    {
        $gedcom = $this->gedcom(__DIR__ . '/../../res/dates-service/initial.ged');
        $this->service()->removeDeatBuriCremYifDateNotEmpty($gedcom);
        $this->fixGedcomModifiedDate($gedcom);
        $this->assertEquals(
            "{$this->gedcom(__DIR__ . '/../../res/dates-service/removeDeatBuriCremYifDateNotEmpty.ged')}",
            "{$gedcom}"
        );
    }

    public function test_addDatePlacForBirtDeatBuriCrem()
    {
        $gedcom = $this->gedcom(__DIR__ . '/../../res/dates-service/initial.ged');
        $this->service()->addDatePlacForBirtDeatBuriCrem($gedcom);
        $this->fixGedcomModifiedDate($gedcom);
        $this->assertEquals(
            "{$this->gedcom(__DIR__ . '/../../res/dates-service/addDatePlacForBirtDeatBuriCrem.ged')}",
            "{$gedcom}"
        );
    }
}
