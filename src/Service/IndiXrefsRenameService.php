<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\YearInterface;
use Zebooka\Gedcom\Model\DateFactory;

class IndiXrefsRenameService extends XrefsRenameServiceAbstract
{
    const REGEXP = '/^(?<prefix>I|A)(?<year>\d+|____)(?<name>[A-Z]+)(?<sequence>\d+)?/';

    public function getNodes(Document $gedcom): \DOMNodeList
    {
        return $gedcom->indiNode();
    }

    public function composeNodeXref(\DOMElement $famNode, Document $gedcom): ?string
    {
        if ('' === ($name = $this->transliteratedName($famNode, $gedcom))) {
            return null;
        }
        return substr($this->prefix($famNode, $gedcom) . $this->birthYear($famNode, $gedcom) . $name, 0, self::LENGTH_LIMIT_55X);
    }

    public function prefix(\DOMElement $indiNode, Document $gedcom)
    {
        $birthDate = DateFactory::fromString($gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $indiNode));
        return ($birthDate instanceof DateCalendarInterface ? 'I' : 'A');
    }

    public function birthYear(\DOMElement $indiNode, Document $gedcom)
    {
        $birthDate = DateFactory::fromString($gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $indiNode));
        if ($birthDate instanceof YearInterface) {
            return $birthDate->year();
        } else {
            return '____';
        }
    }

    public function transliteratedName(\DOMElement $indiNode, Document $gedcom)
    {
        // TODO add usage of Roman name
        $nameNode = $gedcom->xpath('./G:NAME[G:TYPE[@value="birth"]] | ./G:NAME[last()]', $indiNode)->item(0);
        $surn = trim($gedcom->xpath('string(./G:SURN/@value)', $nameNode));
        $givn = trim($gedcom->xpath('string(./G:GIVN/@value)', $nameNode));
        return $this->transliterateService->transliterateId("{$surn} {$givn}");
    }
}
