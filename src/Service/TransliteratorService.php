<?php

namespace Zebooka\Gedcom\Service;

class TransliteratorService
{
//    const CYRILLIC = 'ru-ru_Latn/BGN; Latin; ASCII; [\'·] Remove';
    const CYRILLIC = 'ru-ru_Latn/BGN; Latin; ASCII; NFD; [:Nonspacing Mark:] Remove; [\'ʹ·] Remove; NFC';

    /** @var \Transliterator */
    private $transliterator;
    /** @var string|null */
    private $romanizedType;

    public function __construct(string $transliteratorId = self::CYRILLIC, ?string $romanizedType = null)
    {
        $this->transliterator = \Transliterator::create($transliteratorId);
        $this->romanizedType = $romanizedType;
    }

    public function transliterate(string $string): string
    {
        return $this->transliterator->transliterate($string);
    }

    public function transliterateId(string $string, string $regexp = '/[^A-Z0-9_]/i'): string
    {
        return strtoupper(preg_replace($regexp, '', $this->transliterate($string)));
    }

    public function romanizedType()
    {
        return $this->romanizedType;
    }
}
