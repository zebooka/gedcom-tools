<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

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
        $xpaths = [
            '//G:BIRT',
            '//G:DEAT',
            '//G:BURI',
            '//G:CREM',
        ];
        foreach ($xpaths as $xpath) {
            $nodes = $gedcom->xpath($xpath);
            foreach ($nodes as $node) {
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
            }
        }
    }

    public function setDeatBuriCremYifDateEmpty(Document $gedcom)
    {
        $xpaths = [
            '//G:DEAT[not(@value) and (G:DATE[not(@value)] or not(G:DATE))]',
            '//G:BURI[not(@value) and (G:DATE[not(@value)] or not(G:DATE))]',
            '//G:CREM[not(@value) and (G:DATE[not(@value)] or not(G:DATE))]',
        ];

        foreach ($xpaths as $xpath) {
            $nodes = $gedcom->xpath($xpath);
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $node->setAttribute('value', 'Y');
            }
        }
    }

    public function removeDeatBuriCremYifDateNotEmpty(Document $gedcom)
    {
        $xpaths = [
            '//G:DEAT[@value and G:DATE[@value]]',
            '//G:BURI[@value and G:DATE[@value]]',
            '//G:CREM[@value and G:DATE[@value]]',
        ];

        foreach ($xpaths as $xpath) {
            $nodes = $gedcom->xpath($xpath);
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $node->removeAttribute('value');
            }
        }
    }
}
