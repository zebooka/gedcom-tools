<?php

namespace Zebooka\Gedcom;

class Formatter
{
    public static function formatXML($xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        return self::formatDOMDocument($dom);
    }

    public static function formatDOMDocument(\DOMDocument $dom)
    {
        $gedcom = '';
        foreach ($dom->documentElement->childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                $line = self::composeLinesFromElement($childNode, 0);
                $gedcom .= ($line !== '' ? $line . PHP_EOL : '');
            }
        }
        return $gedcom;
    }

    public static function formatSimpleXMLElement(\SimpleXMLElement $sxml)
    {
        return self::formatDOMDocument(dom_import_simplexml($sxml)->ownerDocument);
    }

    /**
     * @param \DOMElement $element
     * @param int $level
     * @return string
     */
    public static function composeLinesFromElement(\DOMElement $element, $level)
    {
        $gedcom = '';
        if ($element->namespaceURI === Document::XML_NAMESPACE) {
            $gedcom = "{$level} "
                . ($element->getAttribute('id') ? "@{$element->getAttribute('id')}@ " : '')
                . ($element->localName)
                . (strlen('' . $element->getAttribute('xref')) ? " @{$element->getAttribute('xref')}@" : '')
                . (strlen('' . $element->getAttribute('escape')) ? " @#{$element->getAttribute('escape')}@" : '')
                . (strlen('' . $element->getAttribute('value')) ? " {$element->getAttribute('value')}" : '');
            foreach ($element->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $line = self::composeLinesFromElement($childNode, $level + 1);
                    $gedcom .= ($line !== '' ? PHP_EOL . $line : '');
                }
            }
        }
        return $gedcom;
    }
}
