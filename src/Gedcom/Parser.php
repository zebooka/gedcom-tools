<?php

namespace Zebooka\Gedcom;

class Parser
{
    const ENCODING_ANSEL = 'ANSEL';
    const ENCODING_ASCII = 'ASCII';
    const ENCODING_UNICODE = 'UNICODE';
    const ENCODING_UTF8 = 'UTF-8';

    const LINE_REGEXP = '/^\s*(?<level>[1-9]?[0-9])(?: @(?<xref>.{1,80})@)?(?: (?<tag>[A-Z0-9_]{0,32}))(?: (?:(?:@(?<pointer>[^#].*)@)|((?:@#(?<escape>.+)@ )?(?<value>.*))))?$/i';

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
            . (isset($matches['xref']) && '' !== $matches['xref'] ? ' xref="' . htmlspecialchars($matches['xref']) . '"' : '')
            . (isset($matches['pointer']) && '' !== $matches['pointer'] ? ' pointer="' . htmlspecialchars($matches['pointer']) . '"' : '')
            . (isset($matches['escape']) && '' !== $matches['escape'] ? ' escape="' . htmlspecialchars($matches['escape']) . '"' : '')
            . (isset($matches['value']) && '' !== $matches['value'] ? ' value="' . htmlspecialchars($matches['value']) . '"' : '')
            . '>';
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
        if ("\xEF\xBB\xBF" == substr($gedcom, 0, 3)) {
            $encoding = 'UTF-8';
            $gedcom = substr($gedcom, 3);
        } else {
            $encoding = 'UTF-8';
        }
        // TODO add encoding and namespace
        $gedcom = explode("\n", str_replace("\r", "\n", $gedcom));
        $xml = sprintf('<?xml version="1.0" encoding="%s"?>', $encoding) . PHP_EOL
            . sprintf('<GEDCOM xmlns="%s">', htmlspecialchars(Document::XML_NAMESPACE));
        $stack = [];
        foreach ($gedcom as $line) {
            $xml .= self::createElementFromLine($line, $stack);
        }
        $xml .= self::closeTags($stack) . '</GEDCOM>';
        return $xml;
    }
}
