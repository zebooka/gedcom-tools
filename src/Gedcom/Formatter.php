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
                $gedcom .= self::composeLinesFromElement($childNode, 0) . PHP_EOL;
            }
        }
        return $gedcom;
    }

    public static function formatSimpleXMLElement(\SimpleXMLElement $sxml)
    {
        $gedcom = '';
        foreach ($sxml->children() as $childNode) {
            $gedcom .= self::composeLinesFromElement($childNode, 0) . PHP_EOL;
        }
        return $gedcom;
    }

    /**
     * @param \DOMElement|\SimpleXMLElement $element
     * @param int $level
     * @return string
     */
    public static function composeLinesFromElement($element, $level)
    {
        if ($element instanceof \DOMElement) {
            $gedcom = "{$level} "
                . ($element->getAttribute('id') ? "@{$element->getAttribute('id')}@ " : '')
                . ($element->nodeName)
                . (strlen('' . $element->getAttribute('xref')) ? " @{$element->getAttribute('xref')}@" : '')
                . (strlen('' . $element->getAttribute('escape')) ? " @#{$element->getAttribute('escape')}@" : '')
                . (strlen('' . $element->getAttribute('value')) ? " {$element->getAttribute('value')}" : '');

            foreach ($element->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $gedcom .= PHP_EOL . self::composeLinesFromElement($childNode, $level + 1);
                }
            }
        } elseif ($element instanceof \SimpleXMLElement) {
            $gedcom = "{$level} "
                . ($element['id'] ? "@{$element['id']}@ " : '')
                . ($element->getName())
                . (strlen('' . $element['xref']) ? " @{$element['xref']}@" : '')
                . (strlen('' . $element['escape']) ? " @#{$element['escape']}@" : '')
                . (strlen('' . $element['value']) ? " {$element['value']}" : '');
            ;
            foreach ($element->children() as $childNode) {
                $gedcom .= PHP_EOL . self::composeLinesFromElement($childNode, $level + 1);
            }
        } else {
            throw new \UnexpectedValueException('Unsupported element class: ' . get_class($element));
        }

        return $gedcom;
    }
}
