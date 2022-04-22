<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

class RomanizeService
{
    use UpdateNodeValueTrait;

    /** @var TransliteratorService */
    protected $transliterateService;
    /** @var UpdateModifiedService */
    protected $updateModifiedService;

    public function __construct(TransliteratorService $transliterateService, UpdateModifiedService $updateModifiedService)
    {
        $this->transliterateService = $transliterateService;
        $this->updateModifiedService = $updateModifiedService;
    }

    public function fixSpaceAroundFamilyName(Document $gedcom)
    {
        $nodes = $gedcom->xpath('//G:INDI/G:NAME');
        $nodesUpdated = false;
        foreach ($nodes as $node) {
            /** @var \DOMElement $node */
            $old = trim($node->getAttribute('value'));
            if (preg_match('#^(.*[^\s])/(.*)/$#', $old, $m)) {
                $node->setAttribute('value', "{$m[1]} /{$m[2]}/");
                $this->updateModifiedService->updateNodeModificationDate($gedcom, $node);
                $nodesUpdated = true;
            }
        }
        if ($nodesUpdated) {
            $this->updateModifiedService->updateGedcomModificationDate($gedcom);
        }
    }

    public function romanizeNames(Document $gedcom, $forceRewrite = false)
    {
        $names = $gedcom->xpath('//G:INDI/G:NAME');
        $nodesUpdated = false;
        foreach ($names as $name) {
            /** @var \DOMElement $name */
            if (!$gedcom->xpath('./G:ROMN', $name)->length || $forceRewrite) {
                $nodesUpdated = true;
                $romn = $this->updateNodeValue($gedcom, $name, 'ROMN', $this->transliterateService->transliterate($name->getAttribute('value')));
                foreach (['NPFX', 'GIVN', 'NICK', 'SPFX', 'SURN', 'NSFX'] as $tag) {
                    if ($namePart = $gedcom->xpath("./G:{$tag}", $name)->item(0)) {
                        $this->updateNodeValue($gedcom, $romn, $tag, $this->transliterateService->transliterate($namePart->getAttribute('value')));
                    }
                }
                if ($romanizedType = $this->transliterateService->romanizedType()) {
                    $this->updateNodeValue($gedcom, $romn, 'TYPE', $romanizedType);
                } elseif ($types = $gedcom->xpath('./G:TYPE', $romn)) {
                    foreach ($types as $type) {
                        /** @var \DOMElement $type */
                        $type->remove();
                    }
                }
                $this->updateModifiedService->updateNodeModificationDate($gedcom, $name);
            }
        }
        if ($nodesUpdated) {
            $this->updateModifiedService->updateGedcomModificationDate($gedcom);
        }
    }
}
