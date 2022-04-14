<?php

namespace Zebooka\Gedcom\Model;

class IndiRanking
{
    /** @var Indi */
    private $indi;

    /** @var float */
    private $ranking;

    public function __construct(Indi $indi, float $ranking)
    {
        $this->indi = $indi;
        $this->ranking = $ranking;
    }

    public function indi(): Indi
    {
        return $this->indi;
    }

    public function ranking(): float
    {
        return $this->ranking;
    }
}
