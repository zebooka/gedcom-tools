<?php

namespace Zebooka\Gedcom;

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
