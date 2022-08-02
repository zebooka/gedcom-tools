<?php

namespace Zebooka\Gedcom\Model;

use Zebooka\Gedcom\Formatter;
use Zebooka\Gedcom\Model\Date\DateCalendar\DateCalendarInterface;
use Zebooka\Gedcom\Model\Date\YearInterface;

class IndiMedia
{
    /** @var Indi */
    private $indi;
    /** @var \SplFileInfo */
    private $directory;
    /** @var object|mixed */
    private $meta;

    public function __construct(Indi $indi, \SplFileInfo $directory, $meta)
    {
        $this->indi = $indi;
        $this->directory = $directory;
        $this->meta = $meta;
    }

    public function indi(): Indi
    {
        return $this->indi;
    }

    public function directory(): \SplFileInfo
    {
        return $this->directory;
    }

    public function meta()
    {
        return (object)array_merge([
            'isIndi' => true,
            'xref' => $this->indi->xref(),
            'gedcom' => Formatter::composeLinesFromElement($this->indi->node(), 0),
        ], (array)$this->meta);
    }

    public static function composeDirectoryName(Indi $indi): ?string
    {
        $dates = self::composeYearsOfLife($indi);
        $surn = self::composeFamilyName($indi);
        $givn = self::composeGivenName($indi);
        if (!$surn || !$givn) {
            return null;
        }
        return ($dates ? "{$dates} " : '') . "{$surn} {$givn}";
    }

    public static function composeYearsOfLife(Indi $indi): ?string
    {
        $dates = [];
        $birt = $indi->birt();
        if ($birt instanceof YearInterface) {
            $dates[] = $birt->year();
            $deat = $indi->deat();
            if ($deat instanceof YearInterface) {
                $dates[] = ($birt instanceof DateCalendarInterface ? '-' : '~');
                $dates[] = $deat->year();
                $dates[] = ($deat instanceof DateCalendarInterface ? '' : '~');
            } else {
                $dates[] = ($birt instanceof DateCalendarInterface ? '' : '~');
            }
        }
        return ($dates ? implode('', $dates) : null);
    }

    public static function composeFamilyName(Indi $indi): ?string
    {
        return $indi->xpath('string(./G:NAME/G:SURN/@value)');//->item(0);
    }

    public static function composeGivenName(Indi $indi): ?string
    {
        return str_replace(',', '', $indi->xpath('string(./G:NAME/G:GIVN/@value)'));//->item(0));
    }
}
