<?php

namespace Zebooka\Gedcom;

class Document
{
    const XML_NAMESPACE = 'https://zebooka.com/gedcom/';

    private $sxml;
    private $dom;

    private function __construct(\SimpleXMLElement $sxml)
    {
        $this->sxml = $sxml;
        $this->dom = dom_import_simplexml($this->sxml);
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
     * @return \SimpleXMLElement
     */
    public function sxml()
    {
        return $this->sxml;
    }

    /**
     * @return \DOMElement
     */
    public function dom()
    {
        return $this->dom;
    }

    public function xpath()
    {
    }


}
