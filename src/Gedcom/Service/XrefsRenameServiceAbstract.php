<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

abstract class XrefsRenameServiceAbstract
{
    const LENGTH_LIMIT_55X = 20;
    const REGEXP = '/^(?<prefix>[A-Z])(?<year>\d+|____)(?<name>[A-Z]+)(?<sequence>\d+)?$/';

    /** @var TransliteratorService */
    protected $transliterateService;
    /** @var UpdateModifiedService */
    protected $updateModifiedService;

    public function __construct(TransliteratorService $transliterateService, UpdateModifiedService $updateModifiedService)
    {
        $this->transliterateService = $transliterateService;
        $this->updateModifiedService = $updateModifiedService;
    }

    public function renameXrefs(Document $gedcom, $renameMap = [], $forceRename = false)
    {
        $renameMap = $this->collectXrefsToRename($gedcom, $renameMap, $forceRename);
        $nodesUpdated = false;
        foreach ($renameMap as $from => $to) {
            $nodes = $gedcom->xpath("//*[@xref='{$from}'] | //*[@pointer='{$from}']");
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $nodeUpdated = false;
                if ($node->getAttribute('xref') === $from) {
                    $node->setAttribute('xref', $to);
                    $nodeUpdated = $nodesUpdated = true;
                }
                if ($node->getAttribute('pointer') === $from) {
                    $node->setAttribute('pointer', $to);
                    $nodeUpdated = $nodesUpdated = true;
                }
                if ($nodeUpdated) {
                    $this->updateModifiedService->updateNodeModificationDate($gedcom, $node);
                }
            }
        }
        if ($nodesUpdated) {
            $this->updateModifiedService->updateGedcomModificationDate($gedcom);
        }
        return $renameMap;
    }

    abstract protected function getNodes(Document $gedcom): \DOMNodeList;

    public function collectXrefsToRename(Document $gedcom, $heap = [], $forceRename = false)
    {
        foreach ($this->getNodes($gedcom) as $node) {
            /** @var \DOMElement $node */
            $oldXref = $node->getAttribute('xref');
            $isComposedXref = $this->isComposedXref($oldXref, $gedcom);
            if (!$forceRename && $isComposedXref) {
                continue;
            }
            if (null === ($newXref = $this->composeNodeXref($node, $gedcom))) {
                continue;
            }
            if ($forceRename && $isComposedXref && $this->isSameSeqencedXref($oldXref, $newXref)) {
                continue;
            }
            while (!$this->isXrefAvailable($newXref, $heap, $gedcom)) {
                $newXref = $this->increaseXrefSequence($newXref);
            }
            if ($oldXref === $newXref) {
                continue;
            }
            $heap[$oldXref] = $newXref;
        }
        return $heap;
    }

    public function isComposedXref(string $xref, Document $gedcom): bool
    {
        if (strlen($xref) === 0) {
            throw new \UnexpectedValueException('Unexpected empty XREF value.');
        }
        return preg_match(static::REGEXP, $xref) && ($gedcom->isVersion55x() ? strlen($xref) <= static::LENGTH_LIMIT_55X : true);
    }

    public function isXrefAvailable(string $xref, array $heap, Document $gedcom): bool
    {
        if (in_array($xref, $heap)) {
            return false;
        }
        Document::validateXref($xref);
        return !$gedcom->xpath("//*[@xref='{$xref}']")->count() || array_key_exists($xref, $heap);
    }

    public function isSameSeqencedXref(string $oldXref, string $newXref): bool
    {
        $oldXrefPart = preg_replace('/(\d+)$/', '', $oldXref);
        return 0 === strpos($newXref, $oldXrefPart);
    }

    abstract public function composeNodeXref(\DOMElement $node, Document $gedcom): ?string;

    public function increaseXrefSequence(string $xref)
    {
        Document::validateXref($xref);
        if (!preg_match(static::REGEXP, $xref, $m)) {
            throw new \UnexpectedValueException("XREF '$xref' does not match regular expression.");
        }
        $sequence = (!empty($m['sequence']) ? $m['sequence'] + 1 : 2);
        $prefix = $m['prefix'] . $m['year'] . $m['name'];
        return (strlen($prefix . $sequence) > static::LENGTH_LIMIT_55X
            ? substr($prefix, 0, static::LENGTH_LIMIT_55X - strlen($sequence)) . $sequence
            : $prefix . $sequence);
    }
}
