<?php

namespace Zebooka\Gedcom\Model;

class Leaf
{
    /** @var \DOMElement */
    public $indi;
    /** @var float */
    public $ranking;
    /** @var bool */
    public $isFresh;

    public function __construct(\DOMElement $indi, float $ranking, bool $isFresh)
    {
        $this->indi = $indi;
        $this->ranking = $ranking;
        $this->isFresh = $isFresh;
    }

    public function id()
    {
        return $this->indi->getAttribute('xref');
    }
}
