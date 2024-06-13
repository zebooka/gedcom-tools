<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Formatter;
use Zebooka\Gedcom\Model\Date\DateInterface;
use function Zebooka\Gedcom\descriptionOfAncestorNode;
use function Zebooka\Gedcom\extractLatitude;
use function Zebooka\Gedcom\extractLongitude;
use function Zebooka\Gedcom\modificationTimeOfAncestorNode;
use function Zebooka\Gedcom\numberToSuperscript;
use function Zebooka\Gedcom\xrefOfAncestorNode;

class GpxWaypoint
{
    private $latitude;
    private $longitude;

    private $xrefs = [];
    private $timestamps = [];
    private $types = [];
    private $dates = [];
    private $names = [];
    private $places = [];

    private static $sortWeights = [
        'BIRT' => -100,
        'RESI' => -90,
        'MARR' => -50,
        'OCCU' => -30,
        'DEAT' => 80,
        'CREM' => 90,
        'BURI' => 100,
    ];

    public function __construct(\DOMElement $map, Document $gedcom)
    {
        if ($map->nodeName !== 'MAP' || $map->namespaceURI !== Document::XML_NAMESPACE) {
            throw new \UnexpectedValueException("Unexpected element {$map->nodeName} '{$map->namespaceURI}'. Expecting MAP '{$map->namespaceURI}'.");
        }

        $this->latitude = extractLatitude($map, $gedcom);
        $this->longitude = extractLongitude($map, $gedcom);
        if (null === $this->latitude || null === $this->longitude) {
            throw new \UnexpectedValueException("One or both of coordinates not found or incorrect for MAP: " . Formatter::composeLinesFromElement($map, 3));
        }

        $this->xrefs[] = $xref = xrefOfAncestorNode($map);
        $this->timestamps[] = modificationTimeOfAncestorNode($map, $gedcom);
        $this->types[] = $map->parentNode->parentNode->nodeName;
        $this->names[] = (descriptionOfAncestorNode($xref, $gedcom) ?? $xref);
        $this->places[] = implode(', ', array_filter(array_map('trim', (array)explode(',', (string)$map->parentNode->getAttribute('value'))), 'strlen'));
        if ($date = $gedcom->xpath('string(./G:DATE/@value)', $map->parentNode->parentNode)) {
            $this->dates[] = DateFactory::fromString($date);
        } else {
            $this->dates[] = null;
        }
    }

    public function appendWaypoint(GpxWaypoint $waypoint)
    {
        if ($waypoint->latitude() !== $this->latitude || $waypoint->longitude() !== $this->longitude) {
            throw new \UnexpectedValueException(
                'Waypoints have different coordinates: '
                . json_encode($this->coordinates()) . ' and ' . json_encode($waypoint->coordinates())
            );
        }

        $this->xrefs = array_merge($this->xrefs, $waypoint->xrefs);
        $this->timestamps = array_merge($this->timestamps, $waypoint->timestamps);
        $this->types = array_merge($this->types, $waypoint->types);
        $this->dates = array_merge($this->dates, $waypoint->dates);
        $this->names = array_merge($this->names, $waypoint->names);
        $this->places = array_merge($this->places, $waypoint->places);

        $this->sort();
    }

    /**
     * @return float[]
     */
    public function coordinates(): array
    {
        return [$this->latitude, $this->longitude];
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    public function timestamp(): ?int
    {
        return max($this->timestamps);
    }

    public function name(): string
    {
        $names = array_count_values($this->types);
        return implode('+', array_map(function ($type, $count) {
            return $type . numberToSuperscript($count);
        }, array_keys($names), $names));
    }

    public function description(): string
    {
        $names = array_unique(
            array_map(function (string $type, ?DateInterface $date, string $name) {
                return ($date ? "{$type} ({$date}) - {$name}" : "{$type} - {$name}");
            }, $this->types, $this->dates, $this->names)
        );
        $places = array_unique($this->places);

        return trim(implode("\n", $names) . "\n\n" . implode("\n", $places));
    }

    public function comment(): string
    {
        return trim(implode("\n", $this->xrefs));
    }

    public function type(): ?string
    {
        $types = array_unique($this->types);
        return (count($types) === 1 ? $types[0] : null);
    }

    public function sort(): void
    {
        $points = array_map(
            function ($xref, $timestamp, $type, $date, $name, $place) {
                return [$xref, $timestamp, $type, $date, $name, $place];
            },
            $this->xrefs, $this->timestamps, $this->types, $this->dates, $this->names, $this->places
        );
        usort($points, function ($a, $b) {
            return ((self::$sortWeights[$a[2]] ?? 0) <=> (self::$sortWeights[$b[2]] ?? 0))
                ?: ($a[0] <=> $b[0]);
        });
        $this->xrefs = $this->timestamps = $this->types = $this->dates = $this->names = $this->places = [];
        foreach ($points as list($xref, $timestamp, $type, $date, $name, $place)) {
            $this->xrefs[] = $xref;
            $this->timestamps[] = $timestamp;
            $this->types[] = $type;
            $this->dates[] = $date;
            $this->names[] = $name;
            $this->places[] = $place;
        }
    }
}
