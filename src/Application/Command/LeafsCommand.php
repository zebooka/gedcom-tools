<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Model\IndiRanking;
use Zebooka\Gedcom\Service\LeafsService;

class LeafsCommand extends AbstractCommand
{
    const OPTION_REVERSE = 'reverse';

    protected static $defaultName = 'leafs';

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Display leafs')
            ->setHelp('Search and display leafs from GEDCOM suitable for rendering tree. Leafs are ending descendants on family tree.');

        $this->addOption(self::OPTION_REVERSE, 'r', InputOption::VALUE_NONE, 'Reverse order of leafs (first with higher ranking, least with lower).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $gedcom = $this->getGedcom($input, $output);
        $rankingPrecision = ($output->isVerbose() || $output->isQuiet()) ? 9 : 0;

        $c = new LeafsService();

        $indiRankings = $c->gedcomToIndiRankings($gedcom);
        /** @var IndiRanking $end */
        $end = end($indiRankings);

        if ($input->getOption(self::OPTION_REVERSE)) {
            $indiRankings = array_reverse($indiRankings, true);
            $err->writeln("--> <fg=red>Output in reverse order</>", OutputInterface::VERBOSITY_VERBOSE);
        }

        $rankingLength = strlen('' . number_format($end->ranking(), $rankingPrecision));

        foreach ($indiRankings as $id => $indiRanking) {
            $ranking = str_pad(number_format($indiRanking->ranking(), $rankingPrecision), $rankingLength, ' ', STR_PAD_LEFT);
            $name = $gedcom->xpath('string(./G:NAME/@value)', $indiRanking->indi()->node());
            $birthday = $gedcom->xpath('string(./G:BIRT/G:DATE/@value)', $indiRanking->indi()->node());
            $deathday = $gedcom->xpath('string(./G:DEAT/G:DATE/@value|./G:DEAT/@value)', $indiRanking->indi()->node());
            $dates = ($birthday ?: 'Y') . ($deathday ? "<fg=red> .. {$deathday}</>" : '');

            if ($output->isQuiet() && $indiRanking->indi()->isLeaf()) {
                $output->writeln(number_format($indiRanking->ranking(), $rankingLength) . "\t" . $indiRanking->indi()->xref(), OutputInterface::VERBOSITY_QUIET);
            } else {
                $color = ($indiRanking->indi()->isDead() ? 'fg=gray' : 'fg=bright-white');
                $output->writeln(
                    "<{$color}>{$ranking} -- " .
                    ($indiRanking->indi()->isLeaf() ? "<fg=black;bg=green>{$indiRanking->indi()->xref()}</>" : $indiRanking->indi()->xref()) .
                    " -- {$name} --</> <fg=green>{$dates}</>",
                    OutputInterface::VERBOSITY_NORMAL
                );
            }
        }

        return 0;
    }
}
