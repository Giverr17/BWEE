<?php

use App\Models\Event;
use App\Models\Founder;
use App\Models\GalleryItem;
use App\Models\Partner;
use App\Models\Program;
use App\Models\RoadmapItem;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->user = User::first() ?? User::factory()->create([
        'email' => 'admin_translate_test@example.com',
    ]);

    Cache::flush();
    config()->set('services.deepl.key', 'fake-deepl-key');
    config()->set('services.deepl.url', 'https://api-free.deepl.com');
    $this->shouldFailDeepL = false;

    Http::fake([
        'https://api-free.deepl.com/*' => function (Request $request) {
            if ($this->shouldFailDeepL) {
                return Http::response(null, 500);
            }

            if (str_ends_with($request->url(), '/usage')) {
                return Http::response([
                    'character_count' => 1250,
                    'character_limit' => 500000,
                ], 200);
            }

            $texts = $request['text'] ?? [];
            $translations = [];
            foreach ($texts as $text) {
                $translations[] = [
                    'detected_source_language' => 'EN',
                    'text' => 'Traduction FR: '.$text,
                ];
            }

            return Http::response(['translations' => $translations], 200);
        },
    ]);
});

it('auto-translates manage-programs, preserves manual French, re-enables on clear, handles 500, and saves properly', function () {
    // 1. Set English field, assert French property is filled from faked DeepL response
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-programs')
        ->call('openBlank')
        ->set('slug', 'auto-prog-test')
        ->set('title_en', 'Youth Leadership Program')
        ->assertSet('title_fr', 'Traduction FR: Youth Leadership Program')
        ->assertSet('translateNote', '');

    // 2. Set French by hand first, then change English, assert French is NOT overwritten
    $component->set('title_fr', 'Programme pour la jeunesse fait main')
        ->set('title_en', 'Updated Youth Leadership Program')
        ->assertSet('title_fr', 'Programme pour la jeunesse fait main');

    // 3. Clear French then change English, assert it translates again
    $component->set('title_fr', '')
        ->set('title_en', 'Brand New Youth Initiative')
        ->assertSet('title_fr', 'Traduction FR: Brand New Youth Initiative');

    // 4. Fake a 500 response and assert the form still works and a note is set
    $this->shouldFailDeepL = true;
    $component->set('title_fr', '')
        ->set('title_en', 'Failure Test Initiative')
        ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

    // Form remains usable
    $component->set('title_fr', 'Secours en francais')
        ->set('description_en', 'This is a valid long description in English.')
        ->set('description_fr', 'Ceci est une description valide en francais.')
        ->call('save')
        ->assertHasNoErrors();

    $program = Program::where('slug', 'auto-prog-test')->firstOrFail();
    expect($program->getTranslation('title', 'en', false))->toBe('Failure Test Initiative');
    expect($program->getTranslation('title', 'fr', false))->toBe('Secours en francais');

    // 5. Assert nothing is stored for a field whose French is empty
    $component->call('edit', $program->id)
        ->set('title_fr', '')
        ->set('description_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $program->refresh();
    expect($program->hasTranslation('title', 'fr'))->toBeFalse();
    expect($program->hasTranslation('description', 'fr'))->toBeFalse();

    $program->delete();
});

it('auto-translates manage-roadmap, preserves manual French, re-enables on clear, handles 500, and saves properly', function () {
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-roadmap')
        ->call('openBlank')
        ->set('slug', 'auto-roadmap-test')
        ->set('title_en', 'Phase One Launch')
        ->assertSet('title_fr', 'Traduction FR: Phase One Launch')
        ->assertSet('translateNote', '');

    // Set French by hand, then change English
    $component->set('title_fr', 'Lancement phase un manuel')
        ->set('title_en', 'Updated Phase One Launch')
        ->assertSet('title_fr', 'Lancement phase un manuel');

    // Clear French, change English, translates again
    $component->set('title_fr', '')
        ->set('title_en', 'Next Step Forward')
        ->assertSet('title_fr', 'Traduction FR: Next Step Forward');

    // Fake 500
    $this->shouldFailDeepL = true;
    $component->set('title_fr', '')
        ->set('title_en', 'Roadmap 500 Title')
        ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

    // Save with and without French
    $component->set('title_fr', 'Titre de secours')
        ->set('description_en', 'Detailed roadmap description in English.')
        ->set('description_fr', 'Description de la feuille de route en francais.')
        ->call('save')
        ->assertHasNoErrors();

    $item = RoadmapItem::where('slug', 'auto-roadmap-test')->firstOrFail();
    expect($item->getTranslation('title', 'fr', false))->toBe('Titre de secours');

    $component->call('edit', $item->id)
        ->set('title_fr', '')
        ->set('description_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $item->refresh();
    expect($item->hasTranslation('title', 'fr'))->toBeFalse();
    expect($item->hasTranslation('description', 'fr'))->toBeFalse();

    $item->delete();
});

it('auto-translates manage-events fields and speakers, handles speaker indexing, 500, and saves properly', function () {
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-events')
        ->call('openBlank')
        ->set('title_en', 'Annual Economic Summit')
        ->assertSet('title_fr', 'Traduction FR: Annual Economic Summit')
        ->set('tag_en', 'Summit')
        ->assertSet('tag_fr', 'Traduction FR: Summit')
        ->set('location_en', 'Toronto Hall')
        ->assertSet('location_fr', 'Traduction FR: Toronto Hall');

    // Speaker auto-translation
    $component->call('addSpeaker')
        ->set('speakers.0.name', 'Dr. Clarke')
        ->set('speakers.0.title_en', 'Keynote Speaker')
        ->assertSet('speakers.0.title_fr', 'Traduction FR: Keynote Speaker');

    // Hand-typed speaker French
    $component->set('speakers.0.title_fr', 'Conférencière d honneur')
        ->set('speakers.0.title_en', 'Updated Speaker Role')
        ->assertSet('speakers.0.title_fr', 'Conférencière d honneur');

    // Speaker alignment on add and remove
    $component->call('addSpeaker')
        ->set('speakers.1.name', 'Second Speaker')
        ->set('speakers.1.title_en', 'Panel Moderator')
        ->assertSet('speakers.1.title_fr', 'Traduction FR: Panel Moderator');

    $component->call('removeSpeaker', 0);
    // Previous speaker 1 is now speaker 0 and kept its title
    expect($component->get('speakers.0.name'))->toBe('Second Speaker');

    // Fake 500
    $this->shouldFailDeepL = true;
    $component->set('title_fr', '')
        ->set('title_en', 'Summit With 500 Error')
        ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

    // Save
    $component->set('event_date', Carbon::now()->addMonths(2)->format('Y-m-d'))
        ->set('title_fr', 'Sommet de secours')
        ->call('save')
        ->assertHasNoErrors();

    $event = Event::orderBy('id', 'desc')->firstOrFail();
    expect($event->getTranslation('title', 'fr', false))->toBe('Sommet de secours');

    $component->call('edit', $event->id)
        ->set('title_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $event->refresh();
    expect($event->hasTranslation('title', 'fr'))->toBeFalse();

    $event->delete();
});

it('auto-translates manage-founders, preserves manual French, re-enables on clear, handles 500, and saves properly', function () {
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-founders')
        ->call('openBlank')
        ->set('name', 'Angela Davis')
        ->set('role_en', 'Executive Director')
        ->assertSet('role_fr', 'Traduction FR: Executive Director')
        ->assertSet('translateNote', '');

    // Set French by hand, then change English
    $component->set('role_fr', 'Directrice générale manuelle')
        ->set('role_en', 'Senior Executive Director')
        ->assertSet('role_fr', 'Directrice générale manuelle');

    // Clear French, change English
    $component->set('role_fr', '')
        ->set('role_en', 'Managing Director')
        ->assertSet('role_fr', 'Traduction FR: Managing Director');

    // Fake 500
    $this->shouldFailDeepL = true;
    $component->set('role_fr', '')
        ->set('role_en', 'Director Under 500')
        ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

    $component->set('role_fr', 'Directrice de secours')
        ->set('bio_en', 'Bio description for testing in English.')
        ->call('save')
        ->assertHasNoErrors();

    $founder = Founder::where('name', 'Angela Davis')->firstOrFail();
    expect($founder->getTranslation('role', 'fr', false))->toBe('Directrice de secours');

    $component->call('edit', $founder->id)
        ->set('role_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $founder->refresh();
    expect($founder->hasTranslation('role', 'fr'))->toBeFalse();

    $founder->delete();
});

it('auto-translates manage-gallery, preserves manual French, re-enables on clear, handles 500, and saves properly', function () {
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('openBlank')
        ->set('type', 'video')
        ->set('video_url_input', 'https://youtu.be/dQw4w9WgXcQ')
        ->set('title_en', 'Launch Highlights Video')
        ->assertSet('title_fr', 'Traduction FR: Launch Highlights Video');

    // Manual French
    $component->set('title_fr', 'Moments forts manuel')
        ->set('title_en', 'Updated Launch Highlights')
        ->assertSet('title_fr', 'Moments forts manuel');

    // Clear French re-enables
    $component->set('title_fr', '')
        ->set('title_en', 'Brand New Highlights')
        ->assertSet('title_fr', 'Traduction FR: Brand New Highlights');

    // Fake 500
    $this->shouldFailDeepL = true;
    $component->set('title_fr', '')
        ->set('title_en', 'Highlights 500')
        ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

    $component->set('title_fr', 'Titre de secours video')
        ->call('save')
        ->assertHasNoErrors();

    $gallery = GalleryItem::where('youtube_id', 'dQw4w9WgXcQ')->firstOrFail();
    expect($gallery->getTranslation('title', 'fr', false))->toBe('Titre de secours video');

    $component->call('edit', $gallery->id)
        ->set('title_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $gallery->refresh();
    expect($gallery->hasTranslation('title', 'fr'))->toBeFalse();

    $gallery->delete();
});

it('auto-translates manage-partners, preserves manual French, re-enables on clear, handles 500, and saves properly', function () {
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-partners')
        ->call('openBlank')
        ->set('name', 'York Innovation Hub')
        ->set('note_en', 'Strategic Academic Partner')
        ->assertSet('note_fr', 'Traduction FR: Strategic Academic Partner');

    // Manual French
    $component->set('note_fr', 'Partenaire universitaire manuel')
        ->set('note_en', 'Updated Academic Partner')
        ->assertSet('note_fr', 'Partenaire universitaire manuel');

    // Clear French
    $component->set('note_fr', '')
        ->set('note_en', 'Global Academic Partner')
        ->assertSet('note_fr', 'Traduction FR: Global Academic Partner');

    // Fake 500
    $this->shouldFailDeepL = true;
    $component->set('note_fr', '')
        ->set('note_en', 'Partner Under 500')
        ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

    $component->set('note_fr', 'Note de secours')
        ->call('save')
        ->assertHasNoErrors();

    $partner = Partner::where('name', 'York Innovation Hub')->firstOrFail();
    expect($partner->getTranslation('note', 'fr', false))->toBe('Note de secours');

    $component->call('edit', $partner->id)
        ->set('note_fr', '')
        ->call('save')
        ->assertHasNoErrors();

    $partner->refresh();
    expect($partner->hasTranslation('note', 'fr'))->toBeFalse();

    $partner->delete();
});

it('auto-translates manage-settings twin rows, preserves manual French, handles 500, and saves properly', function () {
    $base = SiteSetting::firstOrCreate(
        ['key' => 'test_live_tagline'],
        ['value' => 'Building Strong Businesses', 'type' => 'text', 'group' => 'general', 'label' => 'Test Live Tagline']
    );

    $twin = SiteSetting::firstOrCreate(
        ['key' => 'test_live_tagline_fr'],
        ['value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Test Live Tagline FR']
    );

    try {
        $component = Volt::actingAs($this->user)
            ->test('admin.manage-settings')
            ->set("values.{$base->id}", 'Empowering Black Women Entrepreneurs')
            ->assertSet("values.{$twin->id}", 'Traduction FR: Empowering Black Women Entrepreneurs');

        // Set French by hand, then change English
        $component->set("values.{$twin->id}", 'Autonomisation manuelle des femmes')
            ->set("values.{$base->id}", 'Updated English Heading')
            ->assertSet("values.{$twin->id}", 'Autonomisation manuelle des femmes');

        // Clear French, change English
        $component->set("values.{$twin->id}", '')
            ->set("values.{$base->id}", 'Another English Header')
            ->assertSet("values.{$twin->id}", 'Traduction FR: Another English Header');

        // Fake 500
        $this->shouldFailDeepL = true;
        $component->set("values.{$twin->id}", '')
            ->set("values.{$base->id}", 'Heading Under 500 Error')
            ->assertSet('translateNote', 'Automatic translation is unavailable right now. You can type the French yourself.');

        // Save
        $component->set("values.{$twin->id}", 'Titre francais final')
            ->call('save')
            ->assertHasNoErrors();

        expect(SiteSetting::where('key', 'test_live_tagline')->value('value'))->toBe('Heading Under 500 Error');
        expect(SiteSetting::where('key', 'test_live_tagline_fr')->value('value'))->toBe('Titre francais final');
    } finally {
        $base->delete();
        $twin->delete();
    }
});

it('shows translation quota on admin dashboard when available and hides when null', function () {
    Cache::flush();
    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Translation quota: 1,250 of 500,000 characters used this month');

    Cache::flush();
    $this->shouldFailDeepL = true;

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Translation quota:');
});
