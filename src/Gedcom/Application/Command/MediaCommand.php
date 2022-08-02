<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Model\IndiMedia;
use Zebooka\Gedcom\Service\IndiXrefsRenameService;
use Zebooka\Gedcom\Service\MediaStructureService;

class MediaCommand extends AbstractCommand
{
    const ARGUMENT_DIR = 'directory';
    const OPTION_DRY_RUN = 'dry-run';
    const OPTION_OVERWRITE = 'overwrite';

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
        $this->addOption(self::OPTION_OVERWRITE, 'o', InputOption::VALUE_NONE, 'Rename existing directories to match person\'s data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);

        $err->writeln("--> Generating media structure", OutputInterface::VERBOSITY_NORMAL);

        $service = new MediaStructureService($gedcom);

        if (!$input->getOption(self::OPTION_DRY_RUN)) {
            list ($old, $new) = $service->generateStructure(
                new \SplFileInfo($input->getArgument(self::ARGUMENT_DIR)),
                $input->getOption(self::OPTION_OVERWRITE)
            );
        } else {
            $old = $service->readStructure(new \SplFileInfo($input->getArgument(self::ARGUMENT_DIR)));
            $new = $service->readGedcom($old);
        }

        $results = [
            [$old, 'gray', 'bright-white'],
            [$new, 'cyan', 'bright-green'],
        ];
        foreach ($results as list($indiMedias, $color1, $color2)) {
            /** @var IndiMedia[] $indiMedias */
            foreach ($indiMedias as $indi) {
                $dir = $indi->directory()->getRealPath();
                if ($output->isQuiet()) {
                    $output->writeln("{$indi->indi()->xref()}\t{$dir}", OutputInterface::VERBOSITY_QUIET);
                } else {
                    $xrefPadded = str_pad($indi->indi()->xref(), IndiXrefsRenameService::LENGTH_LIMIT_55X, ' ', STR_PAD_RIGHT);
                    $output->writeln("<fg={$color1}>{$xrefPadded} --></> <fg={$color2}>{$dir}</>", OutputInterface::VERBOSITY_NORMAL);
                }
            }
        }

        return 0;
    }
}
