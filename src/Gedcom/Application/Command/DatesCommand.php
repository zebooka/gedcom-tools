<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Service\DatesService;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class DatesCommand extends AbstractCommand
{
    const OPTION_DRY_RUN = 'dry-run';
    const OPTION_NOWRITE_SOFTWARE = 'nowrite-software';

    protected static $defaultName = 'dates';

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Optimize dates')
            ->setHelp('Optimize dates by dropping some of empty tags and adding some default values.');

        $this->addOption(self::OPTION_DRY_RUN, 'd', InputOption::VALUE_NONE, 'Optimize dates, but do not save anything to file.');
        $this->addOption(self::OPTION_NOWRITE_SOFTWARE, 'S', InputOption::VALUE_NONE, 'Do not update software info in the header of modified GEDCOM file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);

        $err->writeln("--> Optimizing dates", OutputInterface::VERBOSITY_NORMAL);

        $c = new DatesService(new UpdateModifiedService(!$input->getOption(self::OPTION_NOWRITE_SOFTWARE)));
        $c->addDatePlacForBirtDeatBuriCrem($gedcom);
        $c->setDeatBuriCremYifDateEmpty($gedcom);
        $c->removeDeatBuriCremYifDateNotEmpty($gedcom);

        if (!$input->getOption(self::OPTION_DRY_RUN)) {
            $this->putGedcom($gedcom, $input, $output);
        }

        return 0;
    }
}
