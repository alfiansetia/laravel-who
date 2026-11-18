<?php

namespace Tests\Unit;

use App\Services\LndServices;
use PHPUnit\Framework\TestCase;

class LndServicesTest extends TestCase
{
    private const HTML = <<<'HTML'
        <html><body>
        <h1><span>&nbsp;</span>&nbsp;<span><span>The Power of Cross-Functional Collaboration</span></span></h1>
        <section class="s_features pt32 pb32"><div class="container"><div class="row">
            <div><a href="/web/login?redirect=https://map.integrasi.online/slides/slide/the-power-of-cross-functional-collaboration-59"><b>Materi</b></a></div>
            <div><a href="/web/login?redirect=https://docs.google.com/forms/d/e/ABC/viewform"><b><i>Post-Test</i></b></a></div>
        </div></div></section>
        <h1><span>&nbsp;</span>&nbsp;<span><span>Strategic Thinking</span></span></h1>
        <section class="s_features pt32 pb32"><div class="container"><div class="row">
            <div><a href="/web/login?redirect=https://youtu.be/KYcNyn7AyiI"><b>Materi</b></a></div>
            <div><a href="https://docs.google.com/forms/d/e/XYZ/viewform"><b><i>Post-Test</i></b></a></div>
        </div></div></section>
        </body></html>
        HTML;

    public function test_parse_programs_extracts_title_materi_and_post_test(): void
    {
        $programs = LndServices::parsePrograms(self::HTML, 'https://map.integrasi.online');

        $this->assertCount(2, $programs);
        $this->assertSame('The Power of Cross-Functional Collaboration', $programs[0]['title']);
        $this->assertSame(
            'https://map.integrasi.online/slides/slide/the-power-of-cross-functional-collaboration-59',
            $programs[0]['materi_url'],
        );
        $this->assertSame('https://docs.google.com/forms/d/e/ABC/viewform', $programs[0]['post_test_url']);
        $this->assertSame('Strategic Thinking', $programs[1]['title']);
        $this->assertSame('https://youtu.be/KYcNyn7AyiI', $programs[1]['materi_url']);
    }

    public function test_parse_programs_resolves_relative_urls_against_base(): void
    {
        $html = <<<'HTML'
            <html><body>
            <h1><span><span>Judul Relatif</span></span></h1>
            <section class="s_features"><a href="/slides/slide/contoh-1">Materi</a><a href="/slides/slide/contoh-2">Post-Test</a></section>
            </body></html>
            HTML;

        $programs = LndServices::parsePrograms($html, 'https://map.integrasi.online');

        $this->assertCount(1, $programs);
        $this->assertSame('https://map.integrasi.online/slides/slide/contoh-1', $programs[0]['materi_url']);
        $this->assertSame('https://map.integrasi.online/slides/slide/contoh-2', $programs[0]['post_test_url']);
    }
}
