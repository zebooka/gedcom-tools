<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Service\DatesService;
use Zebooka\Gedcom\Service\IndiXrefsRenameService;
use Zebooka\Gedcom\Service\MediaStructureService;
use Zebooka\Gedcom\Service\UpdateModifiedService;

class MediaCommand extends AbstractCommand
{
    const ARGUMENT_DIR = 'directory';
    const OPTION_DRY_RUN = 'dry-run';

    protected static $defaultName = 'media';

    protected function configure()
    {
        parent::configure();
        $this->getDefinition()->setArguments([
            new InputArgument(self::ARGUMENT_GEDCOM, InputArgument::REQUIRED, 'GEDCOM file to process.'),
            new InputArgument(self::ARGUMENT_DIR, InputArgument::REQUIRED, 'Directory for scan/output of media structure.'),
        ]);

        $this->setDescription('Media structure')
            ->setHelp('Generate directories for storing persons media.');

        $this->addOption(self::OPTION_DRY_RUN, 'd', InputOption::VALUE_NONE, 'Scan existing directories and read GEDCOM to form list of directories.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);

        $err->writeln("--> Generating media structure", OutputInterface::VERBOSITY_NORMAL);

        $service = new MediaStructureService($gedcom);

        if (!$input->getOption(self::OPTION_DRY_RUN)) {
            $service->generateStructure(new \SplFileInfo($input->getArgument(self::ARGUMENT_DIR)));
        } else {
            $old = $service->readStructure(new \SplFileInfo($input->getArgument(self::ARGUMENT_DIR)));
            $new = $service->readGedcom($old);
            foreach ($old as $indi) {
                $dir = $indi->directory()->getPathname();
                if ($output->isQuiet()) {
                    $output->writeln("{$indi->indi()->xref()}\t{$dir}", OutputInterface::VERBOSITY_QUIET);
                } else {
                    $xrefPadded = str_pad($indi->indi()->xref(), IndiXrefsRenameService::LENGTH_LIMIT_55X, ' ', STR_PAD_RIGHT);
                    $output->writeln("<fg=gray>{$xrefPadded} --></> <fg=bright-white>{$dir}</>", OutputInterface::VERBOSITY_NORMAL);
                }
            }
            foreach ($new as $indi) {
                $dir = $indi->directory()->getPathname();
                if ($output->isQuiet()) {
                    $output->writeln("{$indi->indi()->xref()}\t{$dir}", OutputInterface::VERBOSITY_QUIET);
                } else {
                    $xrefPadded = str_pad($indi->indi()->xref(), IndiXrefsRenameService::LENGTH_LIMIT_55X, ' ', STR_PAD_RIGHT);
                    $output->writeln("<fg=green>{$xrefPadded} --></> <fg=bright-green>{$dir}</>", OutputInterface::VERBOSITY_NORMAL);
                }
            }
        }

        return 0;
    }
}
