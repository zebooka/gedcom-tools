<?php

namespace Test\Zebooka\Gedcom\Model;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Indi;

class IndiTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function gedcom()
    {
        return Document::createFromGedcom(file_get_contents(__DIR__ . '/../../../res/gedcom.ged'));
    }

    public function test_father()
    {
        $gedcom = $this->gedcom();
        $fatherNode = $gedcom->indi('FATHER');
        $father = new Indi($fatherNode, $gedcom);
        $this->assertSame($fatherNode, $father->node());
        $this->assertSame($gedcom, $father->gedcom());
        $this->assertEquals('FATHER', $father->xref());
        $this->assertFalse($father->isDead());
        $this->assertFalse($father->isLeaf());
        $this->assertEquals(2, $father->hasChildren());
        $children = $father->children();
        $this->assertIsArray($children);
        $this->assertCount(2, $children);
        foreach ($children as $child) {
            $this->assertInstanceOf(Indi::class, $child);
            $this->assertContains($child->xref(), ['SON', 'DAUGHTER']);
        }
    }

    public function test_uncle()
    {
        $gedcom = $this->gedcom();
        $uncleNode = $gedcom->indi('UNCLE');
        $uncle = new Indi($uncleNode, $gedcom);
        $this->assertSame($uncleNode, $uncle->node());
        $this->assertSame($gedcom, $uncle->gedcom());
        $this->assertEquals('UNCLE', $uncle->xref());
        $this->assertTrue($uncle->isDead());
        $this->assertFalse($uncle->isLeaf());
        $this->assertEquals(1, $uncle->hasChildren());
        $children = $uncle->children();
        $this->assertIsArray($children);
        $this->assertCount(0, $children);
    }
}
