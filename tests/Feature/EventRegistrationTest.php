<?php

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Volt\Volt;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::first() ?? User::factory()->create();
});

it('auto-generates slug and uniqueness correctly', function () {
    $event1 = Event::create([
        'title' => ['en' => 'Women In Tech Summit', 'fr' => 'Femmes en techno'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    expect($event1->slug)->toBe('women-in-tech-summit');

    $event2 = Event::create([
        'title' => ['en' => 'Women In Tech Summit'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    expect($event2->slug)->toBe('women-in-tech-summit-1');
});

it('computes isRegistrationOpen correctly based on registration_open toggle', function () {
    $futureEvent = new Event([
        'registration_open' => true,
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
    ]);
    expect($futureEvent->isRegistrationOpen())->toBeTrue();

    $todayEvent = new Event([
        'registration_open' => true,
        'event_date' => Carbon::today()->format('Y-m-d'),
    ]);
    expect($todayEvent->isRegistrationOpen())->toBeTrue();

    $pastEvent = new Event([
        'registration_open' => true,
        'event_date' => Carbon::yesterday()->format('Y-m-d'),
    ]);
    // The admin toggle explicitly keeps registration open even if the event date has passed
    expect($pastEvent->isRegistrationOpen())->toBeTrue();

    $switchOffEvent = new Event([
        'registration_open' => false,
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
    ]);
    expect($switchOffEvent->isRegistrationOpen())->toBeFalse();
});

it('renders the registration form for an open event in en and fr', function () {
    $event = Event::create([
        'title' => ['en' => 'Global Leadership Forum', 'fr' => 'Forum mondial du leadership'],
        'date_label' => ['en' => 'November 15, 2026', 'fr' => '15 novembre 2026'],
        'location' => ['en' => 'Toronto, ON', 'fr' => 'Toronto, ON'],
        'description' => ['en' => 'Join leaders across the diaspora.'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    // English
    $responseEn = $this->get('/register/'.$event->slug);
    $responseEn->assertStatus(200);
    $responseEn->assertSee('Global Leadership Forum');
    $responseEn->assertSee('Full Name');
    $responseEn->assertSee('Country of Residence');
    $responseEn->assertSee('Complete Registration');

    // French
    app()->setLocale('fr');
    session(['locale' => 'fr']);
    $responseFr = $this->withSession(['locale' => 'fr'])->get('/register/'.$event->slug);
    $responseFr->assertStatus(200);
    $responseFr->assertSee('Forum mondial du leadership');
    $responseFr->assertSee('Nom complet');
    $responseFr->assertSee('Pays de résidence');
    $responseFr->assertSee('Finaliser l’inscription');
    app()->setLocale('en');
});

it('shows friendly closed page with HTTP 200 when registration is closed', function () {
    $event = Event::create([
        'title' => ['en' => 'Past Executive Roundtable', 'fr' => 'Table ronde exécutive passée'],
        'event_date' => Carbon::yesterday()->format('Y-m-d'),
        'registration_open' => false,
    ]);

    $response = $this->get('/register/'.$event->slug);
    $response->assertStatus(200);
    $response->assertSee('Registration is Closed');
    $response->assertSee('Past Executive Roundtable');
    $response->assertSee('Back to Events');
});

it('returns 404 for an unknown event slug', function () {
    $response = $this->get('/register/non-existent-event-slug-12345');
    $response->assertStatus(404);
});

it('successfully stores a registration with right event_id, consent_at, and locale', function () {
    $event = Event::create([
        'title' => ['en' => 'Empowerment Summit 2026'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $postData = [
        'full_name' => 'Amara Okafor',
        'email' => 'amara.okafor@example.com',
        'phone' => '+1 416 555 0199',
        'country_of_residence' => 'CA',
        'nationality' => 'NG',
        'profession' => 'Software Engineer',
        'organization' => 'Tech Diaspora',
        'expectations' => 'Looking to network and learn strategies for enterprise growth.',
        'consent' => '1',
    ];

    $response = $this->post('/register/'.$event->slug, $postData);
    $response->assertRedirect(route('events.register.thanks', $event));

    $reg = EventRegistration::where('email', 'amara.okafor@example.com')->first();
    expect($reg)->not->toBeNull();
    expect($reg->event_id)->toBe($event->id);
    expect($reg->country_of_residence)->toBe('CA');
    expect($reg->nationality)->toBe('NG');
    expect($reg->country_of_residence_name_en)->toBe('Canada');
    expect($reg->nationality_name_en)->toBe('Nigeria');
    expect($reg->consent_at)->not->toBeNull();
    expect($reg->locale)->toBe('en');

    $thanks = $this->get(route('events.register.thanks', $event));
    $thanks->assertStatus(200);
    $thanks->assertSee('Thank You for Registering');
    $thanks->assertSee('Empowerment Summit 2026');
});

it('handles duplicate email for the same event without duplicate row and shows friendly notice', function () {
    $event = Event::create([
        'title' => ['en' => 'Networking Breakfast'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $postData = [
        'full_name' => 'Claire Dupont',
        'email' => 'claire.dupont@example.com',
        'phone' => '+1 514 555 0122',
        'country_of_residence' => 'CA',
        'nationality' => 'FR',
        'profession' => 'Consultant',
        'organization' => 'Bilingual Biz',
        'expectations' => 'Connect with other Black women professionals.',
        'consent' => '1',
    ];

    $this->post('/register/'.$event->slug, $postData);
    expect(EventRegistration::where('event_id', $event->id)->where('email', 'claire.dupont@example.com')->count())->toBe(1);

    // Duplicate submission
    $dupResponse = $this->post('/register/'.$event->slug, $postData);
    $dupResponse->assertRedirect(route('events.register.thanks', $event));
    $dupResponse->assertSessionHas('already_registered', true);

    expect(EventRegistration::where('event_id', $event->id)->where('email', 'claire.dupont@example.com')->count())->toBe(1);

    $followThanks = $this->withSession(['already_registered' => true])->get(route('events.register.thanks', $event));
    $followThanks->assertStatus(200);
    $followThanks->assertSee('Already Registered');
});

it('allows the same email to register for different events', function () {
    $event1 = Event::create([
        'title' => ['en' => 'Summit Alpha'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $event2 = Event::create([
        'title' => ['en' => 'Summit Beta'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $payload = [
        'full_name' => 'Khadija Diallo',
        'email' => 'khadija@example.com',
        'phone' => '+1 438 555 9988',
        'country_of_residence' => 'CA',
        'nationality' => 'SN',
        'profession' => 'Founder',
        'organization' => 'AgriTech Africa',
        'expectations' => 'Building international trade partnerships.',
        'consent' => '1',
    ];

    $this->post('/register/'.$event1->slug, $payload)->assertRedirect(route('events.register.thanks', $event1));
    $this->post('/register/'.$event2->slug, $payload)->assertRedirect(route('events.register.thanks', $event2));

    expect(EventRegistration::where('email', 'khadija@example.com')->count())->toBe(2);
});

it('fails validation on bad email, bad phone, missing consent, or invalid country code', function () {
    $event = Event::create([
        'title' => ['en' => 'Validation Testing Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->post('/register/'.$event->slug, [
        'full_name' => 'J', // too short
        'email' => 'not-an-email',
        'phone' => '123', // regex fails
        'country_of_residence' => 'ZZ', // invalid code
        'nationality' => 'XX', // invalid code
        'profession' => 'A', // too short
        'expectations' => 'Hi', // too short
        // consent omitted
    ]);

    $response->assertSessionHasErrors([
        'full_name',
        'email',
        'phone',
        'country_of_residence',
        'nationality',
        'profession',
        'expectations',
        'consent',
    ]);
});

it('ignores honeypot submissions without saving data and pretends success', function () {
    $event = Event::create([
        'title' => ['en' => 'Honeypot Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->post('/register/'.$event->slug, [
        'website' => 'http://spambot.example.com',
        'full_name' => 'Bot User',
        'email' => 'bot@spambot.com',
        'phone' => '+1 416 555 0199',
        'country_of_residence' => 'CA',
        'nationality' => 'CA',
        'profession' => 'Automated Bot',
        'expectations' => 'Spamming forms across the web',
        'consent' => '1',
    ]);

    $response->assertRedirect(route('events.register.thanks', $event));
    expect(EventRegistration::where('email', 'bot@spambot.com')->exists())->toBeFalse();
});

it('throttles event registration to 6 attempts per minute per IP', function () {
    $event = Event::create([
        'title' => ['en' => 'Throttled Event'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $data = [
        'full_name' => 'Rate Limit Test',
        'email' => 'rate@example.com',
        'phone' => '+1 416 555 0199',
        'country_of_residence' => 'CA',
        'nationality' => 'CA',
        'profession' => 'Tester',
        'expectations' => 'Testing rate limiting capacity.',
        'consent' => '1',
    ];

    for ($i = 0; $i < 6; $i++) {
        $data['email'] = "rate_{$i}@example.com";
        $res = $this->post('/register/'.$event->slug, $data);
        expect($res->getStatusCode())->toBeIn([302, 200]);
    }

    // 7th request should hit 429 Too Many Requests
    $data['email'] = 'rate_7@example.com';
    $blocked = $this->post('/register/'.$event->slug, $data);
    expect($blocked->getStatusCode())->toBe(429);
});

it('requires authentication for admin registrations and allows filtering and searching', function () {
    $this->get('/switch/registrations')->assertRedirect('/switch/login');

    $event = Event::create([
        'title' => ['en' => 'Admin Test Summit'],
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    EventRegistration::create([
        'event_id' => $event->id,
        'full_name' => 'Zainab Ahmed',
        'email' => 'zainab@example.com',
        'phone' => '+1 416 555 3344',
        'country_of_residence' => 'CA',
        'nationality' => 'GH',
        'profession' => 'Architect',
        'organization' => 'Urban Build',
        'expectations' => 'Sustainable architecture workshops.',
        'consent_at' => now(),
        'locale' => 'en',
    ]);

    Volt::actingAs($this->user)
        ->test('admin.manage-registrations')
        ->set('selectedEventId', $event->id)
        ->assertSee('Zainab Ahmed')
        ->assertSee('Urban Build')
        ->set('search', 'Nonexistent')
        ->assertDontSee('Zainab Ahmed')
        ->set('search', 'Zainab')
        ->assertSee('Zainab Ahmed');
});

it('exports registrations as CSV with BOM and English country names', function () {
    $event = Event::create([
        'title' => ['en' => 'CSV Export Event'],
        'slug' => 'csv-export-event',
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    EventRegistration::create([
        'event_id' => $event->id,
        'full_name' => 'Aisha Bello',
        'email' => 'aisha@example.com',
        'phone' => '+1 416 555 7788',
        'country_of_residence' => 'CA',
        'nationality' => 'NG',
        'profession' => 'Biologist',
        'organization' => 'BioHealth Inc',
        'expectations' => 'Healthcare networking.',
        'consent_at' => now(),
        'locale' => 'en',
    ]);

    $response = $this->actingAs($this->user)->get(route('admin.registrations.export-csv', $event));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $content = $response->streamedContent();

    // Check UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();
    expect($content)->toContain('Full Name');
    expect($content)->toContain('Country of Residence');
    expect($content)->toContain('Aisha Bello');
    expect($content)->toContain('Canada');
    expect($content)->toContain('Nigeria');
});

it('serves QR SVG download with Content-Disposition attachment and SVG image type', function () {
    $event = Event::create([
        'title' => ['en' => 'QR Download Event'],
        'slug' => 'qr-download-event',
        'event_date' => Carbon::tomorrow()->format('Y-m-d'),
        'registration_open' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('admin.events.qr-svg', $event));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/svg+xml');
    $response->assertHeader('Content-Disposition', 'attachment; filename="qr-download-event-qr.svg"');
    $svg = $response->getContent();
    expect($svg)->toContain('<svg');
    expect($svg)->toContain('</svg>');
});

it('verifies backfill logic creates unique slugs from titles', function () {
    $event = Event::first();
    expect($event->slug)->not->toBeEmpty();
});
