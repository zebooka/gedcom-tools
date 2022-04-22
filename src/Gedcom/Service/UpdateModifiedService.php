<?php

namespace Zebooka\Gedcom\Service;

use Zebooka\Gedcom\Document;

class UpdateModifiedService
{
    use UpdateNodeValueTrait;

    const SOUR = 'gedcom-tools';
    const NAME = 'gedcom-tools';
    const CORP = 'Anton Bondar <zebooka@gmail.com>';
    const ADDR = 'https://github.com/zebooka/gedcom-tools';

    public function updateGedcomModificationDate(Document $gedcom)
    {
        /** @var \DOMElement $head */
        $head = $gedcom->xpath('/G:GEDCOM/G:HEAD')->item(0);
        if (!$head) {
            throw new \UnexpectedValueException('Incorrect GEDCOM. No HEAD tag found.');
        }

        $sour = $this->updateNodeValue($gedcom, $head, 'SOUR', self::SOUR);
        $vers = $this->updateNodeValue($gedcom, $sour, 'VERS',
            defined('\\Zebooka\\Gedcom\\Application::VERSION')
                ? constant('\\Zebooka\\Gedcom\\Application::VERSION')
                : '0.0.0-dev'
        );
        $name = $this->updateNodeValue($gedcom, $sour, 'NAME', self::NAME);
        $corp = $this->updateNodeValue($gedcom, $sour, 'CORP', self::CORP);
        $addr = $this->updateNodeValue($gedcom, $corp, 'ADDR', self::ADDR);
    }

    public function updateNodeModificationDate(Document $gedcom, \DOMElement $node)
    {
        while (!$node->hasAttribute('xref')) {
            $node = $node->parentNode;
            if ($node instanceof \DOMDocument) {
                // no parent node with xref
                return;
            }
        }

        $chan = $this->updateNodeValue($gedcom, $node, 'CHAN', null);
        $date = $this->updateNodeValue($gedcom, $chan, 'DATE', strtoupper(date('j M Y')));
        $time = $this->updateNodeValue($gedcom, $date, 'TIME', strtoupper(date('H:i:s')));
    }
}
