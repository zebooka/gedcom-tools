<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

trait UpdateNodeValueTrait
{
    protected function updateNodeValue(Document $gedcom, \DOMElement $parentNode, string $nodeName, ?string $value)
    {
        /** @var \DOMElement $node */
        $node = $gedcom->xpath("./G:{$nodeName}", $parentNode)->item(0);
        if (!$node) {
            $node = $gedcom->dom()->createElementNS(Document::XML_NAMESPACE, $nodeName);
            $parentNode->appendChild($node);
        }
        if (null === $value) {
            $node->removeAttribute('value');
        } else {
            $node->setAttribute('value', $value);
        }
        return $node;
    }
}
