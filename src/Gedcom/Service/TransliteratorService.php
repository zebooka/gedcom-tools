<?php

namespace Zebooka\Gedcom\Service;

class TransliteratorService
{
    /** @var \Transliterator */
    private $transliterator;

    public function __construct()
    {
        $this->transliterator = \Transliterator::create('ru-ru_Latn/BGN; Latin; ASCII; UPPER');
    }

    public function transliterate(string $string, string $regexp = '/[^A-Z0-9_]/i'): string
    {
        return $this->transliterator->transliterate($string);
    }

    public function transliterateId(string $string, string $regexp = '/[^A-Z0-9_]/i'): string
    {
        return preg_replace($regexp, '', $this->transliterate($string));
    }
}
