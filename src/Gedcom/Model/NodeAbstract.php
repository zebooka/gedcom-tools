<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Formatter;
use Zebooka\Gedcom\Model\Date\DateCalendarInterface;

abstract class NodeAbstract
{
    /** @var Document */
    protected $gedcom;

    /** @var \DOMElement */
    protected $node;

    public function __construct(\DOMElement $node, Document $gedcom)
    {
        $this->gedcom = $gedcom;
        $this->node = $node;
    }

    public function node(): \DOMElement
    {
        return $this->node;
    }

    public function gedcom(): Document
    {
        return $this->gedcom;
    }

    public function nodeToGedcomString(): string
    {
        return Formatter::composeLinesFromElement($this->node, 0);
    }

    public function xref(): string
    {
        return $this->node->getAttribute('xref');
    }
}
