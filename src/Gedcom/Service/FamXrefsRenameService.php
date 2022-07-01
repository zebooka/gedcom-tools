<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\YearInterface;
use Zebooka\Gedcom\Model\DateFactory;

class FamXrefsRenameService extends XrefsRenameServiceAbstract
{
    const REGEXP = '/^(?<prefix>F)(?<year>\d+|____)(?<name>[A-Z]+)(?<sequence>\d+)?/';

    public function getNodes(Document $gedcom): \DOMNodeList
    {
        return $gedcom->famNode();
    }

    public function composeNodeXref(\DOMElement $famNode, Document $gedcom): ?string
    {
        $husb = $gedcom->xpath('string(./G:HUSB/@pointer)', $famNode);
        $wife = $gedcom->xpath('string(./G:WIFE/@pointer)', $famNode);
        $husbSurn = ($husb ? $this->transliteratedSurname($gedcom->indiNode($husb), $gedcom) : '');
        $wifeSurn = ($wife ? $this->transliteratedSurname($gedcom->indiNode($wife), $gedcom) : '');
        $surn = ($husbSurn === $wifeSurn ? $husbSurn : $husbSurn . $wifeSurn);
        $year = $this->marriageYear($famNode, $gedcom);

        if ('' === $surn) {
            return null;
        }

        return substr($this->prefix($famNode, $gedcom) . ($year ?? '____') . $surn, 0, self::LENGTH_LIMIT_55X);
    }

    public function prefix(\DOMElement $indiNode, Document $gedcom)
    {
        return 'F';
    }

    public function marriageYear(\DOMElement $famNode, Document $gedcom): ?int
    {
        $marriageDate = DateFactory::fromString($gedcom->xpath('string(./G:MARR/G:DATE/@value)', $famNode));
        if ($marriageDate instanceof YearInterface) {
            return $marriageDate->year();
        } else {
            return null;
        }
    }

    public function transliteratedSurname(\DOMElement $indiNode, Document $gedcom)
    {
        // TODO add usage of Roman name
        return $this->transliterateService->transliterateId(
            $gedcom->xpath(
                'string(./G:SURN/@value)',
                $gedcom->xpath('./G:NAME[G:TYPE[@value="birth"]] | ./G:NAME[last()]', $indiNode)->item(0)
            )
        );
    }
}
