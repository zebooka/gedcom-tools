<?php

namespace Zebooka\Gedcom\Application;

use Symfony\Component\Console\Application;
use Zebooka\Gedcom\Application\Command\DatesCommand;
use Zebooka\Gedcom\Application\Command\GpxCommand;
use Zebooka\Gedcom\Application\Command\IdsRenameCommand;
use Zebooka\Gedcom\Application\Command\LeafsCommand;
use Zebooka\Gedcom\Application\Command\MediaCommand;
use Zebooka\Gedcom\Application\Command\RomanizeCommand;

class ConsoleApplicationFactory
{
    public static function getConsoleApplication()
    {
        $a = new Application(
            basename($_SERVER['argv'][0]),
            (defined('VERSION') ? constant('VERSION') : 'v0.0.0-dev') . (defined('BUILD_TIMSTAMP') ? ' (' . date('Y-m-d H:i:s', constant('BUILD_TIMSTAMP')) . ')' : '')
        );

        $a->add(new DatesCommand());
        $a->add(new IdsRenameCommand());
        $a->add(new MediaCommand());
        $a->add(new LeafsCommand());
        $a->add(new RomanizeCommand());
        $a->add(new GpxCommand());

        return $a;
    }
}
