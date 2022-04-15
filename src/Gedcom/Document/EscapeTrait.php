<?php

namespace Zebooka\Gedcom\Document;

trait EscapeTrait
{
    public static function escape55(string $value): string
    {
        return str_replace('@', '@@', $value);
    }

    public static function escape7(string $value): string
    {
        return ($value !== '' && $value[0] === '@' ? '@' : '') . $value;
    }

    public static function escapeXref(string $xref): string
    {
        self::validateXref($xref);
        return '@' . $xref . '@';
    }

    /**
     * @throws \UnexpectedValueException
     */
    public static function validateXref(string $xref)
    {
        if ($xref === '') {
            throw new \UnexpectedValueException('Empty ID is not allowed.');
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $xref)) {
            throw new \UnexpectedValueException('Only A-Z, 0-9 and _ are allowed in ID.');
        }
    }

    public static function unescape(string $escapedAttr): string
    {
        return str_replace('@@', '@', $escapedAttr);
    }
}
