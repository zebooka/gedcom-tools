<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\DateFactory;

class IndiXrefsRenameService
{
    const PREFIX = 'I';
    const LENGTH_LIMIT = 20;
    const REGEXP = '/^I(?<year>\d+|____)(?<name>[A-Z]+)(?<sequence>\d+)?/';

    /** @var TransliteratorService */
    private $transliterateService;

    public function __construct(TransliteratorService $transliterateService)
    {
        $this->transliterateService = $transliterateService;
    }

    public function renameXrefs(Document $gedcom)
    {
        $renameMap = $this->collectXrefsToRename($gedcom);
        // TODO
    }

    public function collectXrefsToRename(Document $gedcom)
    {
        $heap = [];
        $indiNodes = $gedcom->indiNode();
        foreach ($indiNodes as $indiNode) {
            /** @var \DOMElement $indiNode */
            if ($this->isComposedXref($indiNode->getAttribute('xref'))) {
                continue;
            }
            if ('' === ($newXref = $this->composeNodeXref($indiNode, $gedcom))) {
                continue;
            }
            while (!$this->isXrefAvailable($newXref, $heap, $gedcom)) {
                $newXref = $this->increaseXrefSequence($newXref);
            }
            if ($indiNode->getAttribute('xref') === $newXref) {
                continue;
            }
            $heap[$indiNode->getAttribute('xref')] = $newXref;
        }
        return $heap;
    }

    public function isComposedXref(string $xref): bool
    {
        if (strlen($xref) === 0) {
            throw new \UnexpectedValueException('Unexpected empty XREF value.');
        }
        if (strlen($xref) > 20) {
            return false;
        }
        return preg_match(self::REGEXP, $xref) && strlen($xref) <= self::LENGTH_LIMIT;
    }

    public function isXrefAvailable(string $xref, array $heap, Document $gedcom): bool
    {
        if (in_array($xref, $heap)) {
            return false;
        }
        Document::validateXref($xref);
        return !$gedcom->xpath("//*[@xref='{$xref}']")->count() || array_key_exists($xref, $heap);
    }

    public function composeNodeXref(\DOMElement $indiNode, Document $gedcom): string
    {
        if ('' === ($name = $this->transliteratedName($indiNode, $gedcom))) {
            return '';
        }
        return substr(self::PREFIX . $this->birthYear($indiNode, $gedcom) . $name, 0, self::LENGTH_LIMIT);
    }

    public function birthYear(\DOMElement $indiNode, Document $gedcom)
    {
        $birthDate = DateFactory::fromString($gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $indiNode));
        if ($birthDate instanceof DateCalendarInterface) {
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
        $givn = implode('', array_slice(explode(' ', $givn), 0, 1));
        return $this->transliterateService->transliterateId("{$surn} {$givn}");
    }

    public function increaseXrefSequence(string $xref)
    {
        Document::validateXref($xref);
        if (!preg_match(self::REGEXP, $xref, $m)) throw new \UnexpectedValueException("XREF '$xref' does not match regular expression.");
        $sequence = (!empty($m['sequence']) ? $m['sequence'] + 1 : 2);
        $prefix = 'I' . $m['year'] . $m['name'];
        return (strlen($prefix . $sequence) > self::LENGTH_LIMIT
            ? substr($prefix, 0, self::LENGTH_LIMIT - strlen($sequence)) . $sequence
            : $prefix . $sequence);
    }
}
