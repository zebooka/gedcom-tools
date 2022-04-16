<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateFactory;

class IndiIdsRenameService
{
    /** @var TransliteratorService */
    private $transliterateService;

    public function __construct(TransliteratorService $transliterateService)
    {
        $this->transliterateService = $transliterateService;
    }

    public function renameIds(Document $gedcom)
    {
        $indiNodes = $gedcom->indiNode();
        foreach ($indiNodes as $indiNode) {
            var_dump($indiNode);
        }
    }

    public function transliteratedName(\DOMElement $indiNode, Document $gedcom)
    {
        $nameNode = $gedcom->xpath('./NAME[TYPE[@value="birth"]] | ./NAME[last()]', $indiNode)->item(0);
        $surn = trim($gedcom->xpath('string(./SURN/@value)', $nameNode));
        $givn = trim($gedcom->xpath('string(./GIVN/@value)', $nameNode));
        $givn = implode('', array_slice(explode(' ', $givn), 0, 1));
        return $this->transliterateService->transliterateId("{$surn} {$givn}");
    }

    public function birthYear(\DOMElement $indiNode, Document $gedcom)
    {
        $birthDate = DateFactory::fromString($gedcom->xpath('string(./BIRT/DATE/@value)', $indiNode));
        if ($birthDate instanceof DateCalendarInterface) {
            return $birthDate->year();
        } else {
            return '__';
        }
    }
}
