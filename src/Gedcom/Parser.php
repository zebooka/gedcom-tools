<?php

namespace Zebooka\Gedcom;

class Parser
{
    const ENCODING_ANSEL = 'ANSEL';
    const ENCODING_ASCII = 'ASCII';
    const ENCODING_UNICODE = 'UNICODE';
    const ENCODING_UTF8 = 'UTF-8';

    const LINE_REGEXP = '/^\s*(?<level>[1-9]?[0-9])(?: @(?<xref>.{1,80})@)?(?: (?<tag>[A-Z0-9_]{0,32}))(?: (?:(?:@(?<pointer>[^#].*)@)|(?<value>.*)))?$/i';

    public static function createElementFromLine($line, &$stack)
    {
        $line = ltrim($line, "\r\n\t ");
        $line = rtrim($line, "\r\n");
        if ('' === $line) {
            return '';
        }
        if (!preg_match(self::LINE_REGEXP, $line, $matches)) {
            throw new \UnexpectedValueException("Can not parse line: {$line}");
        }
        if (count($stack) < (int)$matches['level'] - 1) {
            throw new \UnderflowException('Line level does not match previously parsed lines.');
        }
        $close = self::closeTags($stack, (int)$matches['level']);
        array_push($stack, $matches['tag']);
        return $close . '<' . $matches['tag']
            . (isset($matches['xref']) && '' !== $matches['xref'] ? ' xref="' . self::escape($matches['xref']) . '"' : '')
            . (isset($matches['pointer']) && '' !== $matches['pointer'] ? ' pointer="' . self::escape($matches['pointer']) . '"' : '')
            . (isset($matches['escape']) && '' !== $matches['escape'] ? ' escape="' . self::escape($matches['escape']) . '"' : '')
            . (isset($matches['value']) && '' !== $matches['value'] ? ' value="' . self::escape($matches['value']) . '"' : '')
            . '>';
    }

    /**
     * Escape string workaround function
     * @param string $string
     * @return string
     */
    public static function escape(string $string): string
    {
        return str_replace(["\t"],["&#9;"], htmlspecialchars($string));
    }

    public static function closeTags(&$stack, $expectedLevel = 0)
    {
        $close = '';
        while (count($stack) > $expectedLevel) {
            $close .= '</' . array_pop($stack) . '>';
        }
        return $close;
    }

    public static function parseString($gedcom)
    {
        $encoding = 'UTF-8';
        $bom = 0;

        if (Document::BOM == substr($gedcom, 0, 3)) {
            $gedcom = substr($gedcom, 3);
            $bom = 1;
        }

        $gedcom = explode("\n", str_replace("\r", "\n", $gedcom));
        $xmlHead = sprintf('<?xml version="1.0" encoding="%s"?>', $encoding) . PHP_EOL
            . sprintf('<GEDCOM xmlns="%s" bom="%d">', htmlspecialchars(Document::XML_NAMESPACE), $bom);
        $stack = [];
        $xml = '';
        foreach ($gedcom as $line) {
            $xml .= self::createElementFromLine($line, $stack);
        }
        if (!$xml) {
            throw new \UnexpectedValueException('Empty GEDCOM file.');
        }
        $xml .= self::closeTags($stack) . '</GEDCOM>';
        return $xmlHead . $xml;
    }
}
