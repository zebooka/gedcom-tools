<?php

namespace Zebooka\Gedcom\Document;

trait EscapeTrait
{
    /**
     * @param string $value
     * @return string
     */
    public static function escape55($value)
    {
        return str_replace('@', '@@', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function escape7($value)
    {
        return ($value !== '' && $value[0] === '@' ? '@' : '') . $value;
    }


    /**
     * @param string $value
     * @return string
     */
    public static function escapeXref($xref)
    {
        if ($xref === '') {
            throw new \UnexpectedValueException('Empty ID is not allowed.');
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $xref)) {
            throw new \UnexpectedValueException('Only A-Z, 0-9 and _ are allowed in ID.');
        }
        return '@' . $xref . '@';
    }

    /**
     * @param string $value
     * @return string
     */
    public static function unescape($escapedAttr)
    {
        return str_replace('@@', '@', $escapedAttr);
    }
}
