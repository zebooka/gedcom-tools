<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Service\FamXrefsRenameService;
use Zebooka\Gedcom\Service\IndiXrefsRenameService;
use Zebooka\Gedcom\Service\UpdateModifiedService;
use Zebooka\Gedcom\Service\TransliteratorService;

class IdsRenameCommand extends AbstractCommand
{
    const OPTION_DRY_RUN = 'dry-run';

    protected static $defaultName = 'ids';

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Make IDs fancy')
            ->setHelp('Transform IDs of INDI and FAM records to better format.');

        $this->addOption(self::OPTION_DRY_RUN, 'd', InputOption::VALUE_NONE, 'Perform IDs rename, but do not save anything to file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);

        $err->writeln("--> Making IDs fancy", OutputInterface::VERBOSITY_VERBOSE);

        $renameMap = [];
        $t = new TransliteratorService();
        $u = new UpdateModifiedService();
        foreach ([new IndiXrefsRenameService($t, $u), new FamXrefsRenameService($t, $u)] as $service) {
            $renameMap = $input->getOption(self::OPTION_DRY_RUN)
                ? $service->collectXrefsToRename($gedcom, $renameMap)
                : $service->renameXrefs($gedcom, $renameMap);
        }

        if ($output->isQuiet()) {
            if ('-' !== $input->getArgument(self::ARGUMENT_GEDCOM)) {
                foreach ($renameMap as $from => $to) {
                    $output->writeln("{$from}\t{$to}", OutputInterface::VERBOSITY_QUIET);
                }
            }
        } else {
            foreach ($renameMap as $from => $to) {
                $output->writeln("<fg=gray>{$from}</> <fg=cyan>--></> <fg=bright-white>{$to}</>", OutputInterface::VERBOSITY_NORMAL);
            }
        }

        if (!$input->getOption(self::OPTION_DRY_RUN)) {
            $this->putGedcom($gedcom, $input, $output);
        }

        return 0;
    }
}
