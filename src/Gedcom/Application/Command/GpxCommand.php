<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Service\GpxService;

class GpxCommand extends AbstractCommand
{
    protected static $defaultName = 'gpx';

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Generate GPX')
            ->setHelp('Generate GPX file with all coordinates found in MAP tags.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);
        $rankingPrecision = ($output->isVerbose() || $output->isQuiet()) ? 9 : 0;

        $c = new GpxService();
        $gpx = $c->generateGpx($gedcom);

        $output->write($gpx, OutputInterface::VERBOSITY_QUIET);

        return 0;
    }
}
