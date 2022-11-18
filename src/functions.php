<?php

namespace Zebooka\Gedcom;

use Zebooka\Gedcom\Model\Indi;
use Zebooka\Gedcom\Model\IndiMedia;

function xrefOfAncestorNode(\DOMElement $element): ?string
{
    while (true) {
        if ($element->hasAttribute('xref')) {
            return $element->getAttribute('xref');
        }
        if (!$element->parentNode) {
            break;
        }
        $element = $element->parentNode;

    }
    return null;
}

function modificationTimeOfAncestorNode(\DOMElement $element, Document $gedcom): ?int
{
    while ($element->parentNode && !$gedcom->xpath('./G:CHAN', $element)->length) {
        $element = $element->parentNode;
    }
    $date = $gedcom->xpath('string(./G:CHAN/G:DATE/@value)', $element);
    $time = $gedcom->xpath('string(./G:CHAN/G:DATE/G:TIME/@value)', $element);
    $unix = strtotime(trim("{$date} {$time}"));

    return ($unix !== false ? $unix : null);
}

function extractLatitude(\DOMElement $map, Document $gedcom): ?float
{
    $lati = trim($gedcom->xpath('string(./G:LATI/@value)', $map));
    if ($lati && preg_match('/^(N|S)(\d+(?:\.\d+)?)$/', $lati, $matches) && -90 <= $matches[2] && $matches[2] <= 90) {
        return ('S' == $matches[1] ? -1 : 1) * floatval($matches[2]);
    }
    return null;
}

function extractLongitude(\DOMElement $map, Document $gedcom): ?float
{
    $long = trim($gedcom->xpath('string(./G:LONG/@value)', $map));
    if ($long && preg_match('/^(E|W)(\d+(?:\.\d+)?)$/', $long, $matches) && -180 <= $matches[2] && $matches[2] <= 180) {
        return ('W' == $matches[1] ? -1 : 1) * floatval($matches[2]);
    }
    return null;
}

function descriptionOfAncestorNode($xref, Document $gedcom): ?string
{
    if ($indiNode = $gedcom->indiNode($xref)) {
        return IndiMedia::composeDirectoryName(new Indi($indiNode, $gedcom));
    } elseif ($famNode = $gedcom->famNode($xref)) {
        $husb = $gedcom->xpath('string(./G:HUSB/@pointer)', $famNode);
        if ($husb && $husb = $gedcom->indiNode($husb)) {
            $husb = IndiMedia::composeDirectoryName(new Indi($husb, $gedcom));
        } else {
            $husb = '?';
        }

        $wife = $gedcom->xpath('string(./G:WIFE/@pointer)', $famNode);
        if ($wife && $wife = $gedcom->indiNode($wife)) {
            $wife = IndiMedia::composeDirectoryName(new Indi($wife, $gedcom));
        } else {
            $wife = '?';
        }

        return "{$husb} + {$wife}";
    } else {
        return null;
    }
}
