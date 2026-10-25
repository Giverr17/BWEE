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
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->user = User::first() ?? User::factory()->create([
        'email' => 'admin_test@example.com',
    ]);
});

it('loads programs admin and edits English and French side by side', function () {
    $this->actingAs($this->user)
        ->get(route('admin.programs'))
        ->assertOk();

    $program = Program::create([
        'slug' => 'test-program-bilingual',
        'title' => ['en' => 'Initial Title EN'],
        'description' => ['en' => 'Initial Description EN with enough length'],
        'sort_order' => 99,
        'is_active' => true,
    ]);

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-programs')
            ->call('edit', $program->id)
            ->set('title_en', 'Updated Program EN')
            ->set('title_fr', 'Programme mis a jour FR')
            ->set('description_en', 'Updated Program Description EN with enough length')
            ->set('description_fr', 'Description du programme en francais avec assez de texte')
            ->call('save')
            ->assertHasNoErrors();

        $program->refresh();
        expect($program->getTranslation('title', 'en', false))->toBe('Updated Program EN');
        expect($program->getTranslation('title', 'fr', false))->toBe('Programme mis a jour FR');
        expect($program->getTranslation('description', 'en', false))->toBe('Updated Program Description EN with enough length');
        expect($program->getTranslation('description', 'fr', false))->toBe('Description du programme en francais avec assez de texte');

        Volt::actingAs($this->user)
            ->test('admin.manage-programs')
            ->call('edit', $program->id)
            ->set('title_fr', '')
            ->set('description_fr', '')
            ->call('save')
            ->assertHasNoErrors();

        $program->refresh();
        expect($program->getTranslation('title', 'en', false))->toBe('Updated Program EN');
        expect($program->hasTranslation('title', 'fr'))->toBeFalse();
        expect($program->hasTranslation('description', 'fr'))->toBeFalse();
    } finally {
        $program->delete();
    }
});

it('loads roadmap admin and edits English and French side by side', function () {
    $this->actingAs($this->user)
        ->get(route('admin.roadmap'))
        ->assertOk();

    $item = RoadmapItem::create([
        'slug' => 'test-roadmap-bilingual',
        'title' => ['en' => 'Initial Roadmap EN'],
        'description' => ['en' => 'Initial Description EN with enough length'],
        'sort_order' => 99,
        'is_active' => true,
    ]);

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-roadmap')
            ->call('edit', $item->id)
            ->set('title_en', 'Updated Roadmap EN')
            ->set('title_fr', 'Feuille de route FR')
            ->set('description_en', 'Updated Roadmap Description EN with enough length')
            ->set('description_fr', 'Description de la feuille de route FR assez longue')
            ->call('save')
            ->assertHasNoErrors();

        $item->refresh();
        expect($item->getTranslation('title', 'en', false))->toBe('Updated Roadmap EN');
        expect($item->getTranslation('title', 'fr', false))->toBe('Feuille de route FR');
        expect($item->getTranslation('description', 'en', false))->toBe('Updated Roadmap Description EN with enough length');
        expect($item->getTranslation('description', 'fr', false))->toBe('Description de la feuille de route FR assez longue');

        Volt::actingAs($this->user)
            ->test('admin.manage-roadmap')
            ->call('edit', $item->id)
            ->set('title_fr', '')
            ->set('description_fr', '')
            ->call('save')
            ->assertHasNoErrors();

        $item->refresh();
        expect($item->getTranslation('title', 'en', false))->toBe('Updated Roadmap EN');
        expect($item->hasTranslation('title', 'fr'))->toBeFalse();
        expect($item->hasTranslation('description', 'fr'))->toBeFalse();
    } finally {
        $item->delete();
    }
});

it('loads events admin and edits English and French side by side', function () {
    $this->actingAs($this->user)
        ->get(route('admin.events'))
        ->assertOk();

    $event = Event::create([
        'title' => ['en' => 'Initial Event EN'],
        'tag' => ['en' => 'Launch'],
        'event_date' => Carbon::now()->addMonth()->format('Y-m-d'),
        'date_label' => ['en' => 'Next Month'],
        'location' => ['en' => 'Virtual'],
        'description' => ['en' => 'Initial Description EN with enough length'],
        'cta_text' => ['en' => 'Register'],
        'sort_order' => 99,
        'is_active' => true,
    ]);

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-events')
            ->call('edit', $event->id)
            ->set('title_en', 'Updated Event EN')
            ->set('title_fr', 'Evenement mis a jour FR')
            ->set('tag_en', 'Workshop EN')
            ->set('tag_fr', 'Atelier FR')
            ->set('date_label_en', 'November 2026')
            ->set('date_label_fr', 'Novembre 2026')
            ->set('location_en', 'Toronto, ON')
            ->set('location_fr', 'Toronto (Ontario)')
            ->set('description_en', 'Updated Event Description EN with enough length')
            ->set('description_fr', 'Description de l evenement FR avec assez de caracteres')
            ->set('cta_text_en', 'Join Us EN')
            ->set('cta_text_fr', 'Rejoignez-nous FR')
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();
        expect($event->getTranslation('title', 'en', false))->toBe('Updated Event EN');
        expect($event->getTranslation('title', 'fr', false))->toBe('Evenement mis a jour FR');
        expect($event->getTranslation('tag', 'en', false))->toBe('Workshop EN');
        expect($event->getTranslation('tag', 'fr', false))->toBe('Atelier FR');
        expect($event->getTranslation('date_label', 'en', false))->toBe('November 2026');
        expect($event->getTranslation('date_label', 'fr', false))->toBe('Novembre 2026');
        expect($event->getTranslation('location', 'en', false))->toBe('Toronto, ON');
        expect($event->getTranslation('location', 'fr', false))->toBe('Toronto (Ontario)');
        expect($event->getTranslation('description', 'en', false))->toBe('Updated Event Description EN with enough length');
        expect($event->getTranslation('description', 'fr', false))->toBe('Description de l evenement FR avec assez de caracteres');
        expect($event->getTranslation('cta_text', 'en', false))->toBe('Join Us EN');
        expect($event->getTranslation('cta_text', 'fr', false))->toBe('Rejoignez-nous FR');

        Volt::actingAs($this->user)
            ->test('admin.manage-events')
            ->call('edit', $event->id)
            ->set('title_fr', '')
            ->set('tag_fr', '')
            ->set('date_label_fr', '')
            ->set('location_fr', '')
            ->set('description_fr', '')
            ->set('cta_text_fr', '')
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();
        expect($event->getTranslation('title', 'en', false))->toBe('Updated Event EN');
        expect($event->hasTranslation('title', 'fr'))->toBeFalse();
        expect($event->hasTranslation('tag', 'fr'))->toBeFalse();
        expect($event->hasTranslation('date_label', 'fr'))->toBeFalse();
        expect($event->hasTranslation('location', 'fr'))->toBeFalse();
        expect($event->hasTranslation('description', 'fr'))->toBeFalse();
        expect($event->hasTranslation('cta_text', 'fr'))->toBeFalse();
    } finally {
        $event->delete();
    }
});

it('loads founders admin and edits English and French side by side', function () {
    $this->actingAs($this->user)
        ->get(route('admin.founders'))
        ->assertOk();

    $founder = Founder::create([
        'name' => 'Test Founder Name',
        'role' => ['en' => 'Co-Founder EN'],
        'bio' => ['en' => 'Initial Founder Bio EN with enough length'],
        'sort_order' => 99,
        'is_active' => true,
    ]);

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-founders')
            ->call('edit', $founder->id)
            ->set('role_en', 'Executive Director EN')
            ->set('role_fr', 'Directrice generale FR')
            ->set('bio_en', 'Updated Founder Bio EN with enough length')
            ->set('bio_fr', 'Biographie mise a jour de la fondatrice en francais')
            ->call('save')
            ->assertHasNoErrors();

        $founder->refresh();
        expect($founder->getTranslation('role', 'en', false))->toBe('Executive Director EN');
        expect($founder->getTranslation('role', 'fr', false))->toBe('Directrice generale FR');
        expect($founder->getTranslation('bio', 'en', false))->toBe('Updated Founder Bio EN with enough length');
        expect($founder->getTranslation('bio', 'fr', false))->toBe('Biographie mise a jour de la fondatrice en francais');

        Volt::actingAs($this->user)
            ->test('admin.manage-founders')
            ->call('edit', $founder->id)
            ->set('role_fr', '')
            ->set('bio_fr', '')
            ->call('save')
            ->assertHasNoErrors();

        $founder->refresh();
        expect($founder->getTranslation('role', 'en', false))->toBe('Executive Director EN');
        expect($founder->hasTranslation('role', 'fr'))->toBeFalse();
        expect($founder->hasTranslation('bio', 'fr'))->toBeFalse();
    } finally {
        $founder->delete();
    }
});

it('loads gallery admin and edits English and French side by side', function () {
    $this->actingAs($this->user)
        ->get(route('admin.gallery'))
        ->assertOk();

    $item = GalleryItem::create([
        'title' => ['en' => 'Initial Gallery EN'],
        'caption' => ['en' => 'Initial Gallery Caption EN'],
        'type' => 'video',
        'youtube_id' => 'dQw4w9WgXcQ',
        'sort_order' => 99,
        'is_active' => true,
    ]);

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-gallery')
            ->call('edit', $item->id)
            ->set('title_en', 'Updated Gallery Item EN')
            ->set('title_fr', 'Element de galerie FR')
            ->set('caption_en', 'Updated Gallery Caption EN')
            ->set('caption_fr', 'Legende de galerie mise a jour FR')
            ->call('save')
            ->assertHasNoErrors();

        $item->refresh();
        expect($item->getTranslation('title', 'en', false))->toBe('Updated Gallery Item EN');
        expect($item->getTranslation('title', 'fr', false))->toBe('Element de galerie FR');
        expect($item->getTranslation('caption', 'en', false))->toBe('Updated Gallery Caption EN');
        expect($item->getTranslation('caption', 'fr', false))->toBe('Legende de galerie mise a jour FR');

        Volt::actingAs($this->user)
            ->test('admin.manage-gallery')
            ->call('edit', $item->id)
            ->set('title_fr', '')
            ->set('caption_fr', '')
            ->call('save')
            ->assertHasNoErrors();

        $item->refresh();
        expect($item->getTranslation('title', 'en', false))->toBe('Updated Gallery Item EN');
        expect($item->hasTranslation('title', 'fr'))->toBeFalse();
        expect($item->hasTranslation('caption', 'fr'))->toBeFalse();
    } finally {
        $item->delete();
    }
});

it('loads partners admin and edits English and French side by side', function () {
    $this->actingAs($this->user)
        ->get(route('admin.partners'))
        ->assertOk();

    $partner = Partner::create([
        'name' => 'Test Partner Organization',
        'note' => ['en' => 'Initial Note EN'],
        'sort_order' => 99,
        'is_active' => true,
    ]);

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-partners')
            ->call('edit', $partner->id)
            ->set('note_en', 'Strategic Partner EN')
            ->set('note_fr', 'Partenaire strategique FR')
            ->call('save')
            ->assertHasNoErrors();

        $partner->refresh();
        expect($partner->getTranslation('note', 'en', false))->toBe('Strategic Partner EN');
        expect($partner->getTranslation('note', 'fr', false))->toBe('Partenaire strategique FR');

        Volt::actingAs($this->user)
            ->test('admin.manage-partners')
            ->call('edit', $partner->id)
            ->set('note_fr', '')
            ->call('save')
            ->assertHasNoErrors();

        $partner->refresh();
        expect($partner->getTranslation('note', 'en', false))->toBe('Strategic Partner EN');
        expect($partner->hasTranslation('note', 'fr'))->toBeFalse();
    } finally {
        $partner->delete();
    }
});

it('loads settings admin and saves base and french twin keys', function () {
    $this->actingAs($this->user)
        ->get(route('admin.settings'))
        ->assertOk();

    $base = SiteSetting::firstOrCreate(
        ['key' => 'test_feature_key'],
        ['value' => 'Initial Base Value', 'type' => 'text', 'group' => 'general', 'label' => 'Test Feature']
    );

    $twin = SiteSetting::firstOrCreate(
        ['key' => 'test_feature_key_fr'],
        ['value' => 'Initial Valeur Base FR', 'type' => 'text', 'group' => 'general', 'label' => 'Test Feature FR']
    );

    try {
        Volt::actingAs($this->user)
            ->test('admin.manage-settings')
            ->set("values.{$base->id}", 'Saved Base Setting EN')
            ->set("values.{$twin->id}", 'Parametre FR sauvegarde')
            ->call('save')
            ->assertHasNoErrors();

        expect(SiteSetting::where('key', 'test_feature_key')->value('value'))->toBe('Saved Base Setting EN');
        expect(SiteSetting::where('key', 'test_feature_key_fr')->value('value'))->toBe('Parametre FR sauvegarde');
    } finally {
        $base->delete();
        $twin->delete();
    }
});
