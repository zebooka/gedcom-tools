<?php

namespace Test\Zebooka\Gedcom\Service;

use PHPUnit\Framework\TestCase;
use Zebooka\Gedcom\Service\TransliteratorService;

class TransliteratorServiceTest extends TestCase
{
    public function test_transliterate()
    {
        $service = new TransliteratorService();
        $this->assertEquals('Vasya Pupkin', $service->transliterate('Вася Пупкин'));
        $this->assertEquals('Gleb Zhiglov', $service->transliterate('Глеб Жиглов'));
        $this->assertEquals('Volodya Sharapov', $service->transliterate('Володя Шарапов'));
        $this->assertEquals('Elochka Lyudoyed', $service->transliterate('Элочка Людоед'));
        $this->assertEquals('Nelli Yovovich', $service->transliterate('Нэлли Йовович'));
    }
}
