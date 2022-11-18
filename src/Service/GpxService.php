<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Formatter;
use Zebooka\Gedcom\Model\GpxWaypoint;
use Zebooka\Gedcom\Model\Indi;
use Zebooka\Gedcom\Model\IndiMedia;

class GpxService
{
    public function generateGpx(Document $gedcom): ?string
    {
        $maps = $gedcom->xpath('//G:MAP');
        if (!$maps->length) {
            return null;
        }

        $software = htmlspecialchars($this->software());

        $minlat = $minlon = $maxlat = $maxlon = $maxtime = null;
        /** @var GpxWaypoint[] $waypoints */
        $waypoints = [];
        foreach ($maps as $map) {
            /** @var GpxWaypoint $waypoint */
            if ($waypoint = $this->mapNodeToWaypoint($map, $gedcom)) {
                $key = json_encode($waypoint->coordinates());
                if (!isset($waypoints[$key])) {
                    $waypoints[$key] = $waypoint;
                    $minlat = min($minlat, $waypoint->latitude()) ?? $waypoint->latitude();
                    $minlon = min($minlon, $waypoint->longitude()) ?? $waypoint->longitude();
                    $maxlat = max($maxlat, $waypoint->latitude()) ?? $waypoint->latitude();
                    $maxlon = max($maxlon, $waypoint->longitude()) ?? $waypoint->longitude();
                } else {
                    $waypoints[$key]->appendWaypoint($waypoint);
                }

                $maxtime = max($maxtime, $waypoint->timestamp()) ?? $waypoint->timestamp();
            }
        }
        if (!count($waypoints)) {
            return null;
        }
        if (null === $maxtime) {
            $maxtime = time();
        }

        $gpx = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="{$software}" xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">
    <metadata>
        <name>{$gedcom->xpath('string(/G:GEDCOM/G:HEAD/G:NOTE/@value)')}</name>
        <time>{$this->formatTime($maxtime)}</time>
        <bounds minlat="{$minlat}" minlon="$minlon" maxlat="{$maxlat}" maxlon="{$maxlon}"/>
    </metadata>

GPX;
        foreach ($waypoints as $waypoint) {
            $gpx .= $this->mapWaypointToGpx($waypoint);
        }

        $gpx .= "</gpx>";
        return $gpx;
    }

    public function software()
    {
        return UpdateModifiedService::NAME
            . ' '
            . (defined('Zebooka\Gedcom\Application::VERSION') ? constant('Zebooka\Gedcom\Application::VERSION') : 'v0.0.0-dev')
            . ' '
            . UpdateModifiedService::ADDR;
    }

    public function mapWaypointToGpx(GpxWaypoint $waypoint)
    {
        return <<<GPX
    <wpt lat="{$waypoint->latitude()}" lon="{$waypoint->longitude()}">
        <time>{$this->formatTime($waypoint->timestamp())}</time>
        <name></name>
        <cmt><![CDATA[{$waypoint->comment()}]]></cmt>
        <desc><![CDATA[{$waypoint->description()}]]></desc>
        <type>{$waypoint->type()}</type>
    </wpt>

GPX;
    }

    public function mapNodeToWaypoint(\DOMElement $map, Document $gedcom): ?GpxWaypoint
    {
        try {
            return new GpxWaypoint($map, $gedcom);
        } catch (\UnexpectedValueException $e) {
            return null;
        }
    }


    public function formatTime(?int $unix = null): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $unix);
    }
}
