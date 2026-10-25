<?php

use App\Services\DeepLTranslator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Cache::flush();
    config()->set('services.deepl.key', 'fake-deepl-key');
    config()->set('services.deepl.url', 'https://api-free.deepl.com');
});

test('restore converts le Dr. Nikki Clarke to la Dre Nikki Clarke', function () {
    $translator = new DeepLTranslator;

    expect($translator->restore('le Dr. Nikki Clarke'))->toBe('la Dre Nikki Clarke');
    expect($translator->restore('le Dr Nikki Clarke'))->toBe('la Dre Nikki Clarke');
    expect($translator->restore('Le Dr. Nikki Clarke'))->toBe('la Dre Nikki Clarke');
    expect($translator->restore('du Dr. Nikki Clarke'))->toBe('de la Dre Nikki Clarke');
    expect($translator->restore('Dr. Nikki Clarke'))->toBe('Dre Nikki Clarke');
    expect($translator->restore('Prof. Clarke'))->toBe('Professeure Clarke');
});

test('restore allows emails and BWEE to survive after tag stripping', function () {
    $translator = new DeepLTranslator;

    $text = '<keep>BWEE</keep> empowers entrepreneurs. Contact <keep>team@bwee.org</keep> for details.';
    expect($translator->restore($text))->toBe('BWEE empowers entrepreneurs. Contact team@bwee.org for details.');
});

test('translate sends protected tags and restores clean French with feminine titles', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'translations' => [
                [
                    'detected_source_language' => 'EN',
                    'text' => 'Bienvenue au <keep>BWEE</keep> avec le Dr. Nikki Clarke, contactez <keep>info@bwee.org</keep>.',
                ],
            ],
        ], 200),
    ]);

    $translator = new DeepLTranslator;
    $result = $translator->translate([
        'intro' => 'Welcome to BWEE with Dr. Nikki Clarke, contact info@bwee.org.',
    ]);

    expect($result)->toHaveKey('intro');
    expect($result['intro'])->toBe('Bienvenue au BWEE avec la Dre Nikki Clarke, contactez info@bwee.org.');
});
