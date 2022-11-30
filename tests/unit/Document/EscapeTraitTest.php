<?php

namespace Zebooka\Gedcom\Document;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Document;

class EscapeTraitTest extends TestCase
{
    public function test_escape55()
    {
        $this->assertEquals('', Document::escape55(''));
        $this->assertEquals('email@@example.com', Document::escape55('email@example.com'));
        $this->assertEquals('@@me and @@them', Document::escape55('@me and @them'));
    }

    public function test_escape7()
    {
        $this->assertEquals('', Document::escape7(''));
        $this->assertEquals('email@example.com', Document::escape7('email@example.com'));
        $this->assertEquals('@@me and @them', Document::escape7('@me and @them'));
    }

    public function test_unescape()
    {
        $this->assertEquals('', Document::unescape(''));
        $this->assertEquals('email@example.com', Document::unescape('email@@example.com'));
        $this->assertEquals('email@example.com', Document::unescape('email@example.com'));
        $this->assertEquals('@me and @them', Document::unescape('@@me and @@them'));
        $this->assertEquals('@me and @she', Document::unescape('@@me and @she'));
    }

    public function test_escapeXref()
    {
        $this->assertEquals('@TEST_123@', Document::escapeXref('TEST_123'));
    }

    public function test_escapeXref_empty_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Empty ID is not allowed.'));
        Document::escapeXref('');
    }

    public function test_escapeXref_invalid_chars_exception()
    {
        $this->expectExceptionObject(new \UnexpectedValueException('Only A-Z, 0-9 and _ are allowed in ID.'));
        Document::escapeXref('@123');
    }
}
