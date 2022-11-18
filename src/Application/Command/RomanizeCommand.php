<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Service\RomanizeService;
use Zebooka\Gedcom\Service\UpdateModifiedService;
use Zebooka\Gedcom\Service\TransliteratorService;

class RomanizeCommand extends AbstractCommand
{
    const OPTION_DRY_RUN = 'dry-run';
    const OPTION_SPACE_ONLY = 'space-only';
    const OPTION_OVERWRITE = 'overwrite';
    const OPTION_TRANSLITERATION = 'transliteration';
    const OPTION_ROMANIZED_TYPE = 'romanized-type';
    const OPTION_NOWRITE_SOFTWARE = 'nowrite-software';

    protected static $defaultName = 'romanize';

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Romanize names')
            ->setHelp('Romanize names and fix Ancestris space issue in NAME tag.');

        $this->addOption(self::OPTION_DRY_RUN, 'd', InputOption::VALUE_NONE, 'Perform romanizing, but do not save anything to file.');
        $this->addOption(self::OPTION_NOWRITE_SOFTWARE, 'S', InputOption::VALUE_NONE, 'Do not update software info in the header of modified GEDCOM file.');
        $this->addOption(self::OPTION_OVERWRITE, 'o', InputOption::VALUE_NONE, 'Overwrite existing romanizations.');
        $this->addOption(self::OPTION_TRANSLITERATION, 't', InputOption::VALUE_REQUIRED, 'Transliteration ID. See php/icu docs for examples.', TransliteratorService::CYRILLIC);
        $this->addOption(self::OPTION_ROMANIZED_TYPE, 'r', InputOption::VALUE_REQUIRED, 'Romanized TYPE to write in tag. See GEDCOM docs.');
        $this->addOption(self::OPTION_SPACE_ONLY, 's', InputOption::VALUE_NONE, 'Only fix spaces in names. No romanization applied.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);

        $t = new TransliteratorService($input->getOption(self::OPTION_TRANSLITERATION), $input->getOption(self::OPTION_ROMANIZED_TYPE));
        $u = new UpdateModifiedService(!$input->getOption(self::OPTION_NOWRITE_SOFTWARE));
        $service = new RomanizeService($t, $u);
        $err->writeln("--> Fixing names spaces", OutputInterface::VERBOSITY_NORMAL);
        $service->fixSpaceAroundFamilyName($gedcom);
        if (!$input->getOption(self::OPTION_SPACE_ONLY)) {
            $err->writeln("--> Romanizing names", OutputInterface::VERBOSITY_NORMAL);
            $service->romanizeNames($gedcom, $input->getOption(self::OPTION_OVERWRITE));
        }

        if (!$input->getOption(self::OPTION_DRY_RUN)) {
            $this->putGedcom($gedcom, $input, $output);
        }

        return 0;
    }
}
