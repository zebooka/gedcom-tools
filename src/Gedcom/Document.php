<?php

namespace Zebooka\Gedcom;

use Zebooka\Gedcom\Document\EscapeTrait;
use Zebooka\Gedcom\Document\ShortcutsTrait;
use Zebooka\Gedcom\Document\VersionTrait;

class Document
{
    use EscapeTrait,
        ShortcutsTrait,
        VersionTrait;

    const XML_NAMESPACE = 'https://zebooka.com/gedcom/';

    private $sxml;
    private $dom;

    private function __construct(\SimpleXMLElement $sxml)
    {
        $this->sxml = $sxml;
        $this->dom = dom_import_simplexml($this->sxml)->ownerDocument;
    }

    /**
     * @param string $gedcom
     * @return Document
     * @throws \Exception
     */
    public static function createFromGedcom($gedcom)
    {
        return new self(new \SimpleXMLElement(Parser::parseString($gedcom)));
    }

    public function __toString()
    {
        return Formatter::formatSimpleXMLElement($this->sxml);
    }

    /**
     * @param \SimpleXMLElement|\DOMNode $node
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function sxml($node = null)
    {
        if ($node instanceof \DOMNodeList) {
            $nodes = [];
            foreach ($node as $subnode) {
                $nodes[] = simplexml_import_dom($subnode);
            }
            return $nodes;
        }
        return ($node ? simplexml_import_dom($node) : $this->sxml);
    }

    /**
     * @return \DOMDocument
     */
    public function dom()
    {
        return $this->dom;
    }

    public function xpath($expression, $contextNode = null)
    {
        $xpath = new \DOMXPath($this->dom);
        $xpath->registerNamespace('G', Document::XML_NAMESPACE);
        return $xpath->evaluate($expression, $contextNode, true);
    }
}
