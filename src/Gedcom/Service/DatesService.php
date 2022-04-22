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

    public function setDeatYifDeatDateEmpty(Document $gedcom)
    {
        $nodes = $gedcom->xpath('//G:DEAT[not(@value) and G:DATE[not(@value)]]');
        foreach ($nodes as $node) {
            /** @var \DOMElement $node */
            $node->setAttribute('value', 'Y');
        }
    }

    public function removeDeatYifDeatDateNotEmpty(Document  $gedcom)
    {
        $nodes = $gedcom->xpath('//G:DEAT[@value and G:DATE[@value]]');
        foreach ($nodes as $node) {
            /** @var \DOMElement $node */
            $node->removeAttribute('value');
        }
    }

    public function removeEmptyDateFromElementsExceptBirtAndDeat(Document  $gedcom)
    {
        $nodes = $gedcom->xpath('//*[local-name() != "BIRT" and local-name() != "DEAT"]/G:DATE[not(@value) and not(child::*)]');
        foreach ($nodes as $node) {
            /** @var \DOMElement $node */
            $node->parentNode->removeChild($node);
        }
    }
}
