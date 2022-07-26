<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Formatter;

class DatesService
{
    use UpdateNodeValueTrait;

    /** @var UpdateModifiedService */
    protected $updateModifiedService;

    public function __construct(UpdateModifiedService $updateModifiedService)
    {
        $this->updateModifiedService = $updateModifiedService;
    }

    public function addDatePlacForBirtDeatBuriCrem(Document $gedcom)
    {
        $nodesUpdated = false;
        $xpaths = [
            '//G:BIRT',
            '//G:DEAT',
            '//G:BURI',
            '//G:CREM',
        ];

        foreach ($xpaths as $xpath) {
            $nodes = $gedcom->xpath($xpath);
            foreach ($nodes as $node) {
                $oldGedcom = Formatter::composeLinesFromElement($node, 1);

                /** @var \DOMElement $node */
                $dateNodes = $gedcom->xpath('./G:DATE', $node);
                if ($dateNodes->length === 0) {
                    $dateNode = $gedcom->dom()->createElementNS(Document::XML_NAMESPACE, 'DATE');
                    if ($node->firstChild) {
                        $node->insertBefore($dateNode, $node->firstChild);
                    }
                    $node->appendChild($dateNode);
                } else {
                    $dateNode = $dateNodes->item(0);
                }
                if ($gedcom->xpath('./G:PLAC', $node)->length === 0) {
                    $placNode = $gedcom->dom()->createElementNS(Document::XML_NAMESPACE, 'PLAC');
                    if ($dateNode->nextSibling) {
                        $node->insertBefore($placNode, $dateNode->nextSibling);
                    } else {
                        $node->appendChild($placNode);
                    }
                }
                $newGedcom = Formatter::composeLinesFromElement($node, 1);
                if ($newGedcom !== $oldGedcom) {
                    $nodesUpdated = true;
                    $this->updateModifiedService->updateNodeModificationDate($gedcom, $node);
                }
            }
        }

        if ($nodesUpdated) {
            $this->updateModifiedService->updateGedcomModificationDate($gedcom);
        }
    }

    public function setDeatBuriCremYifDateEmpty(Document $gedcom)
    {
        $nodesUpdated = false;
        $xpaths = [
            '//G:DEAT[not(@value) and (G:DATE[not(@value)] or not(G:DATE))]',
            '//G:BURI[not(@value) and (G:DATE[not(@value)] or not(G:DATE))]',
            '//G:CREM[not(@value) and (G:DATE[not(@value)] or not(G:DATE))]',
        ];

        foreach ($xpaths as $xpath) {
            $nodes = $gedcom->xpath($xpath);
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                if ('Y' !== $node->getAttribute('value')) {
                    $node->setAttribute('value', 'Y');
                    $nodesUpdated = true;
                    $this->updateModifiedService->updateNodeModificationDate($gedcom, $node);
                }
            }
        }

        if ($nodesUpdated) {
            $this->updateModifiedService->updateGedcomModificationDate($gedcom);
        }
    }

    public function removeDeatBuriCremYifDateNotEmpty(Document $gedcom)
    {
        $nodesUpdated = false;
        $xpaths = [
            '//G:DEAT[@value and G:DATE[@value]]',
            '//G:BURI[@value and G:DATE[@value]]',
            '//G:CREM[@value and G:DATE[@value]]',
        ];

        foreach ($xpaths as $xpath) {
            $nodes = $gedcom->xpath($xpath);
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                if ($node->hasAttribute('value')) {
                    $node->removeAttribute('value');
                    $nodesUpdated = true;
                    $this->updateModifiedService->updateNodeModificationDate($gedcom, $node);
                }
            }
        }

        if ($nodesUpdated) {
            $this->updateModifiedService->updateGedcomModificationDate($gedcom);
        }
    }
}
