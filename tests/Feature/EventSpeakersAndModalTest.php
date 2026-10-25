<?php

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::first() ?? User::factory()->create();
});

it('renders the public events page with speakers preview on cards and the details modal', function () {
    $event = Event::create([
        'title' => ['en' => 'Keynote Gala Night', 'fr' => 'Soiree de gala'],
        'tag' => ['en' => 'Gala'],
        'event_date' => Carbon::now()->addMonth()->format('Y-m-d'),
        'date_label' => ['en' => 'Next Month'],
        'location' => ['en' => 'Toronto Convention Centre'],
        'description' => ['en' => 'A wonderful gathering with keynote speakers.'],
        'speakers' => [
            ['name' => 'Dr. Maya Angel', 'title' => ['en' => 'Author & Scholar', 'fr' => 'Autrice'], 'image' => null],
            ['name' => 'Sarah Johnson', 'title' => ['en' => 'CEO, TechFounders', 'fr' => 'PDG'], 'image' => null],
        ],
        'sort_order' => 1,
        'is_active' => true,
        'is_upcoming' => true,
    ]);

    $response = $this->get('/events');
    $response->assertStatus(200);
    $response->assertSee('Keynote Gala Night');
    $response->assertSee('Dr. Maya Angel');
    $response->assertSee('Sarah Johnson');
    $response->assertSee('2 Speakers');
    $response->assertSee('open-event-modal');
});

it('formats speaker localized titles and image URLs correctly via model accessor', function () {
    $event = new Event([
        'speakers' => [
            [
                'name' => 'Jane Doe',
                'title' => ['en' => 'Lead Researcher', 'fr' => 'Chercheuse principale'],
                'image' => 'speakers/speaker1.jpg',
            ],
        ],
    ]);

    app()->setLocale('en');
    $speakersEn = $event->formatted_speakers;
    expect($speakersEn)->toHaveCount(1)
        ->and($speakersEn[0]['name'])->toBe('Jane Doe')
        ->and($speakersEn[0]['title'])->toBe('Lead Researcher')
        ->and($speakersEn[0]['initials'])->toBe('JD')
        ->and($speakersEn[0]['image_url'])->toContain('speakers/speaker1.jpg');

    $event2 = new Event([
        'speakers' => [
            ['name' => 'Dr. Salewa Olawoye', 'title' => 'Director'],
            ['name' => 'Prof. Peju Layiwola', 'title' => 'Founder'],
            ['name' => 'Dr. Nikki Clarke', 'title' => 'Founder'],
        ],
    ]);
    expect($event2->formatted_speakers[0]['initials'])->toBe('SO')
        ->and($event2->formatted_speakers[1]['initials'])->toBe('PL')
        ->and($event2->formatted_speakers[2]['initials'])->toBe('NC');

    app()->setLocale('fr');
    $speakersFr = $event->formatted_speakers;
    expect($speakersFr[0]['title'])->toBe('Chercheuse principale');
    app()->setLocale('en');
});

it('allows uploading a speaker photo in manage-events and stores it in public storage', function () {
    $file = UploadedFile::fake()->image('speaker_photo.jpg', 200, 200);

    Volt::actingAs($this->user)
        ->test('admin.manage-events')
        ->call('openBlank')
        ->set('title_en', 'Speaker Showcase Summit')
        ->set('event_date', Carbon::now()->addMonths(3)->format('Y-m-d'))
        ->call('addSpeaker')
        ->set('speakers.0.name', 'Prof. Amina Bello')
        ->set('speakers.0.title_en', 'Dean of Humanities')
        ->set('speakerUploads.0', $file)
        ->call('save')
        ->assertHasNoErrors();

    $savedEvent = Event::where('title->en', 'Speaker Showcase Summit')->first();
    expect($savedEvent)->not->toBeNull();
    expect($savedEvent->speakers)->toHaveCount(1);
    expect($savedEvent->speakers[0]['name'])->toBe('Prof. Amina Bello');
    expect($savedEvent->speakers[0]['image'])->not->toBeNull();

    Storage::disk('public')->assertExists($savedEvent->speakers[0]['image']);
});
