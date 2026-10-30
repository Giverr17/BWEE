<?php

use App\Models\FaqItem;
use App\Models\Program;
use App\Models\User;
use App\Services\DeepLTranslator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->user = User::first() ?? User::factory()->create([
        'email' => 'admin_test_draft@example.com',
    ]);

    config()->set('services.deepl.key', 'fake-key-for-test');
    config()->set('services.deepl.url', 'https://api-free.deepl.com');
});

it('translator returns null on connection exception', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => fn () => throw new ConnectionException('Could not resolve host'),
    ]);

    $translator = app(DeepLTranslator::class);
    $result = $translator->translate(['title' => 'Hello World']);

    expect($result)->toBeNull();
});

it('translator returns null on timeout', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => fn () => throw new ConnectionException('Connection timed out after 8000 milliseconds'),
    ]);

    $translator = app(DeepLTranslator::class);
    $result = $translator->translate(['title' => 'Hello World']);

    expect($result)->toBeNull();
});

it('translator returns null on 429 status', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => Http::response(['message' => 'Too many requests'], 429),
    ]);

    $translator = app(DeepLTranslator::class);
    $result = $translator->translate(['title' => 'Hello World']);

    expect($result)->toBeNull();
});

it('translator returns null on 500 server error', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => Http::response('Server error', 500),
    ]);

    $translator = app(DeepLTranslator::class);
    $result = $translator->translate(['title' => 'Hello World']);

    expect($result)->toBeNull();
});

it('translator returns null on 456 quota exceeded status', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => Http::response(['message' => 'Quota exceeded'], 456),
    ]);

    $translator = app(DeepLTranslator::class);
    $result = $translator->translate(['title' => 'Hello World']);

    expect($result)->toBeNull();
});

it('translate endpoint requires authentication', function () {
    $response = $this->postJson(route('admin.translate'), [
        'fields' => ['title' => 'Hello World'],
    ]);

    $response->assertStatus(401);
});

it('translate endpoint returns HTTP 200 with translated null when DeepL fails', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => Http::response(null, 500),
    ]);

    $response = $this->actingAs($this->user)->postJson(route('admin.translate'), [
        'fields' => ['title' => 'Empowering Black Women Entrepreneurs'],
    ]);

    $response->assertOk()
        ->assertJson([
            'translated' => null,
            'reason' => 'unavailable',
        ]);
});

it('translate endpoint returns HTTP 200 with translations on success', function () {
    Http::fake([
        'https://api-free.deepl.com/*' => Http::response([
            'translations' => [
                ['detected_source_language' => 'EN', 'text' => 'Autonomiser les femmes'],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($this->user)->postJson(route('admin.translate'), [
        'fields' => ['title' => 'Empowering Women'],
    ]);

    $response->assertOk()
        ->assertJson([
            'translations' => [
                'title' => 'Autonomiser les femmes',
            ],
        ]);
});

it('translate endpoint returns HTTP 200 with rate_limited when rate limit is exceeded', function () {
    RateLimiter::clear('admin-translate:'.$this->user->id);

    // Exhaust the 150/minute rate limit
    for ($i = 0; $i < 150; $i++) {
        RateLimiter::hit('admin-translate:'.$this->user->id, 60);
    }

    $response = $this->actingAs($this->user)->postJson(route('admin.translate'), [
        'fields' => ['title' => 'Another title'],
    ]);

    $response->assertOk()
        ->assertJson([
            'translated' => null,
            'reason' => 'rate_limited',
        ]);

    RateLimiter::clear('admin-translate:'.$this->user->id);
});

it('saving record with empty French field leaves English untouched and saves successfully', function () {
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-faqs')
        ->set('question_en', 'What is the BWEE initiative?')
        ->set('question_fr', '')
        ->set('answer_en', 'BWEE is dedicated to supporting Black women entrepreneurs across Canada.')
        ->set('answer_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $faq = FaqItem::latest('id')->firstOrFail();
    expect($faq->getTranslation('question', 'en', false))->toBe('What is the BWEE initiative?');
    expect($faq->hasTranslation('question', 'fr'))->toBeFalse();
    expect($faq->getTranslation('answer', 'en', false))->toBe('BWEE is dedicated to supporting Black women entrepreneurs across Canada.');
    expect($faq->hasTranslation('answer', 'fr'))->toBeFalse();

    $faq->delete();
});

it('existing French is preserved when updating a record without modifying French', function () {
    $program = Program::create([
        'slug' => 'preserve-fr-test',
        'title' => ['en' => 'Mentorship Program', 'fr' => 'Programme de mentorat'],
        'description' => ['en' => 'Original English description', 'fr' => 'Description originale en francais'],
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Volt::actingAs($this->user)
        ->test('admin.manage-programs')
        ->call('edit', $program->id)
        ->set('title_en', 'Updated Mentorship Program Title')
        ->call('save')
        ->assertHasNoErrors();

    $program->refresh();
    expect($program->getTranslation('title', 'en', false))->toBe('Updated Mentorship Program Title');
    expect($program->getTranslation('title', 'fr', false))->toBe('Programme de mentorat');

    $program->delete();
});
