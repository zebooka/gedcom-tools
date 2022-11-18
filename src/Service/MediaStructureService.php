<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;
use Zebooka\Gedcom\Formatter;
use Zebooka\Gedcom\Model\Indi;
use Zebooka\Gedcom\Model\IndiMedia;

class MediaStructureService
{
    const METADATA_FILE = '.gedcom-tools.json';

    /** @var Document */
    private $gedcom;
    /** @var \SplFileInfo */
    private $directoryForNew;

    public function __construct(Document $gedcom)
    {
        $this->gedcom = $gedcom;
    }

    public function generateStructure(\SplFileInfo $dir, $syncDirNames = false)
    {
        if (!$dir->isDir()) {
            throw new \UnexpectedValueException("File '{$dir->getPathname()}' is not a directory.");
        }
        $this->directoryForNew = $dir;

        $old = $this->readStructure($dir);
        $new = $this->readGedcom($old);
        foreach ($new as $indiMedia) {
            if (!$indiMedia->directory()->isDir()) {
                mkdir($indiMedia->directory()->getPathname(), 0777, true);
            }
            $this->writeMetaForIndi($indiMedia);
        }

        $renamed = [];
        $existingsDirs = [];
        foreach ($old as $indiMedia) {
            $existingsDirs[] = $indiMedia->directory()->getRealPath();
        }
        foreach ($new as $indiMedia) {
            $existingsDirs[] = $indiMedia->directory()->getRealPath();
        }
        if ($syncDirNames) {
            foreach ($old as $i => $indiMedia) {
                if ($indiMedia->directory()->isDir()) {
                    $di = new \SplFileInfo($indiMedia->directory()->getPath() . DIRECTORY_SEPARATOR . IndiMedia::composeDirectoryName($indiMedia->indi()));
                    if ($indiMedia->directory()->getPathname() === $di->getPathname()
                        || ($indiMedia->meta()->blockRename ?? false)) {
                        continue;
                    }
                    if (in_array($di->getPathname(), $existingsDirs)) {
                        $di = new \SplFileInfo($di->getPathname() . " {$indiMedia->indi()->xref()}");
                    }
                    rename($indiMedia->directory()->getPathname(), $di->getPathname());
                    $renamedIndiMedia = new IndiMedia(
                        $indiMedia->indi(),
                        $di,
                        $indiMedia->meta()
                    );
                    $this->writeMetaForIndi($renamedIndiMedia);
                    unset($old[$i]);
                    $renamed[] = $renamedIndiMedia;
                    $existingsDirs[] = $di->getRealPath();
                } else {
                    throw new \RuntimeException("Directory '{$indiMedia->directory()->getPathname()}' for existing media struct '{$indiMedia->indi()->xref()}' does not exist.");
                }
            }
        }

        $this->writeMetaForNew($this->directoryForNew);
        $old = array_values($old);
        $new = array_values(array_merge($new, $renamed));

        return [$old, $new];
    }

    /**
     * @param \SplFileInfo $dir
     * @return IndiMedia[]
     */
    public function readStructure(\SplFileInfo $dir)
    {
        if (!$dir->isDir()) {
            throw new \UnexpectedValueException("File '{$dir->getPathname()}' is not a directory.");
        }
        $this->directoryForNew = $dir;

        $indiMedias = [];
        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->directoryForNew->getRealPath(),
                    \RecursiveDirectoryIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $di
        ) {
            /** @var \SplFileInfo $di */
            $metaFilename = $di->getRealPath() . DIRECTORY_SEPARATOR . self::METADATA_FILE;
            if ($di->isDir() && file_exists($metaFilename)) {
                $meta = $this->readMeta($metaFilename);
                if (!empty($meta->isDirectoryForNew)) {
                    $this->directoryForNew = $di;
                } elseif (!empty($meta->xref) && $node = $this->gedcom->indiNode($meta->xref)) {
                    $indiMedias[$meta->xref] = new IndiMedia(
                        new Indi($node, $this->gedcom),
                        $di,
                        $meta
                    );
                }
            }
        }
        return $indiMedias;
    }

    /**
     * @param IndiMedia[] $excluded
     * @return IndiMedia[]
     */
    public function readGedcom(array $excluded = [])
    {
        $xrefs = array_keys($excluded);
        $indiMedias = [];
        $existingsDirs = [];
        foreach ($excluded as $indi) {
            $existingsDirs[] = $indi->directory()->getPathname();
        }
        foreach ($this->gedcom->indiNode() as $node) {
            $indi = new Indi($node, $this->gedcom);
            if (in_array($indi->xref(), $xrefs)) {
                continue;
            }
            $dirname = IndiMedia::composeDirectoryName($indi);
            if (null === $dirname) {
                continue; // we do not generate dirs for INDIs without names
            }
            $dirname = $this->directoryForNew . DIRECTORY_SEPARATOR . $dirname;
            if (in_array($dirname, $existingsDirs)) {
                $dirname .= " ({$indi->xref()})";
            }
            $existingsDirs[] = $dirname;
            $indiMedias[$indi->xref()] = new IndiMedia(
                $indi,
                new \SplFileInfo($dirname),
                (object)['xref' => $indi->xref()]
            );
        }
        return $indiMedias;
    }

    private function readMeta($filename)
    {
        return json_decode(file_get_contents($filename));
    }

    private function writeMetaForIndi(IndiMedia $indiMedia)
    {
        file_put_contents(
            $indiMedia->directory()->getPathname() . DIRECTORY_SEPARATOR . self::METADATA_FILE,
            json_encode($indiMedia->meta(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function writeMetaForNew(\SplFileInfo $di)
    {
        file_put_contents(
            $di->getPathname() . DIRECTORY_SEPARATOR . self::METADATA_FILE,
            json_encode([
                'isDirectoryForNew' => true,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
