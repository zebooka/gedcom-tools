<?php

namespace Zebooka\Gedcom\Service;

use Jasny\PHPUnit\Constraint\XSDValidation;
use PhpCsFixer\PhpunitConstraintXmlMatchesXsd\Constraint\XmlMatchesXsd;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Service\GpxService;

class GpxServiceTest extends TestCase
{
    private function gedcomWithoutMap()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../res/gedcom.ged'));
    }

    private function gedcomWithMap()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../res/gedcom_with_map.ged'));
    }

    private function gpxXSD()
    {
        return file_get_contents(__DIR__ . '/../../res/gpx.xsd');
    }

    public function test_generateGpx_returns_null_on_gedcom_without_map_tags()
    {
        $service = new GpxService();
        $gpx = $service->generateGpx($g = $this->gedcomWithoutMap());
        $this->assertNull($gpx);
    }

    public function test_generateGpx_returns_gpx_on_gedcom_with_map_tags()
    {
        $service = new GpxService();
        $gpx = $service->generateGpx($g = $this->gedcomWithMap());
        $this->assertIsString($gpx);

        $constraint = new XmlMatchesXsd($this->gpxXSD());
        $this->assertThat($gpx, $constraint);

        $dom = new \DOMDocument();
        $dom->loadXML($gpx);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('GPX', 'http://www.topografix.com/GPX/1/1');
        $wpts = $xpath->query('/GPX:gpx/GPX:wpt');
        $this->assertCount(2, $wpts);
    }
}
