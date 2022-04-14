<?php

namespace Zebooka\Gedcom\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zebooka\Gedcom\Document;

abstract class AbstractCommand extends Command
{
    const ARGUMENT_GEDCOM = 'gedcom';

    protected function configure()
    {
        parent::configure();

        $this->addArgument(self::ARGUMENT_GEDCOM, InputArgument::OPTIONAL, 'GEDCOM file to process', '-');
    }

    protected function getGedcom(InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $filename = $input->getArgument(self::ARGUMENT_GEDCOM);
        if (0 === ftell(STDIN)) {
            $err->writeln("--> Reading from STDIN...", OutputInterface::VERBOSITY_VERBOSE);
            $contents = '';
            while (!feof(STDIN)) {
                $contents .= fread(STDIN, 1024);
            }
        } elseif ($filename && '-' !== $filename) {
            $err->writeln("--> Reading from file '<info>$filename</info>'...", OutputInterface::VERBOSITY_VERBOSE);
            if (!is_file($filename) || !is_readable($filename)) {
                throw new \RuntimeException("Unable to read file '{$filename}'.");
            }
            $contents = file_get_contents($filename);
        } else {
            throw new \RuntimeException("Please provide a filename or pipe template content to STDIN.");
        }

    $b = strlen($contents);
        $err->writeln("--> <info>{$b}</info> bytes read", OutputInterface::VERBOSITY_DEBUG);

        return Document::createFromGedcom($contents);
    }

    protected function putGedcom(Document $gedcom, InputInterface $input, OutputInterface $output)
    {
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $filename = $input->getArgument(self::ARGUMENT_GEDCOM);
        if ($filename && '-' !== $filename) {
            $err->writeln("--> Saving to file '<info>{$filename}</info>'...", OutputInterface::VERBOSITY_VERBOSE);
            $b = file_put_contents($filename, $gedcom->__toString());
            $err->writeln("--> <info>{$b}</info> bytes written", OutputInterface::VERBOSITY_DEBUG);
            $err->writeln("<info>SAVED</info>", OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $err->writeln("--> Sending to STDOUT...", OutputInterface::VERBOSITY_VERBOSE);
            $output->write($gedcom->__toString(), OutputInterface::VERBOSITY_QUIET);
        }
    }
}
