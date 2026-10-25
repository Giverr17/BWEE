<?php

use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

// DatabaseTransactions wraps every test in a transaction that rolls back when the test ends.
// Unlike RefreshDatabase it does NOT run migrate:fresh, so seeders and real data are untouched.
// UploadedFile::fake()->image() creates in-memory images stored in Storage::fake('public'),
// which is a completely isolated virtual disk -- no real files are ever written to disk.
uses(DatabaseTransactions::class);

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
});

// ---------------------------------------------------------------------------
// Admin tests
// ---------------------------------------------------------------------------

it('uploads 3 images, saves them in order, and sets photo_path to the first', function () {
    $files = [
        UploadedFile::fake()->image('a.jpg', 800, 600),
        UploadedFile::fake()->image('b.jpg', 800, 600),
        UploadedFile::fake()->image('c.jpg', 800, 600),
    ];

    Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('openBlank')
        ->set('title_en', 'Album Test Item')
        ->set('type', 'photo')
        ->set('newPhotos', $files)
        ->call('save')
        ->assertHasNoErrors();

    $item = GalleryItem::where('type', 'photo')
        ->orderByDesc('id')
        ->firstOrFail();

    expect($item->photos)->toBeArray()->toHaveCount(3);
    expect($item->photo_path)->toBe($item->photos[0]);

    foreach ($item->photos as $path) {
        Storage::disk('public')->assertExists($path);
    }
});

it('reorders existing photos and updates the cover correctly', function () {
    $item = GalleryItem::create([
        'title' => ['en' => 'Reorder Test'],
        'type' => 'photo',
        'photo_path' => 'gallery/first.jpg',
        'photos' => ['gallery/first.jpg', 'gallery/second.jpg', 'gallery/third.jpg'],
        'sort_order' => 0,
        'is_active' => true,
    ]);

    // Make the second photo the cover via makeCover(1)
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('edit', $item->id);

    $component->call('makeCover', 1)
        ->call('save')
        ->assertHasNoErrors();

    $item->refresh();
    expect($item->photos[0])->toBe('gallery/second.jpg');
    expect($item->photo_path)->toBe('gallery/second.jpg');
});

it('removes a queued photo and deletes the file from storage on save', function () {
    $fileA = UploadedFile::fake()->image('del_a.jpg');
    $fileB = UploadedFile::fake()->image('del_b.jpg');

    // Create with 2 photos.
    Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('openBlank')
        ->set('title_en', 'Delete Photo Test')
        ->set('type', 'photo')
        ->set('newPhotos', [$fileA, $fileB])
        ->call('save')
        ->assertHasNoErrors();

    $item = GalleryItem::where('type', 'photo')->orderByDesc('id')->firstOrFail();
    expect($item->photos)->toHaveCount(2);

    $pathToRemove = $item->photos[1];
    Storage::disk('public')->assertExists($pathToRemove);

    // Edit: remove index 1.
    Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('edit', $item->id)
        ->call('removeExistingPhoto', 1)
        ->call('save')
        ->assertHasNoErrors();

    $item->refresh();
    expect($item->photos)->toHaveCount(1);
    Storage::disk('public')->assertMissing($pathToRemove);
});

it('rejects a file larger than 5 MB', function () {
    $big = UploadedFile::fake()->create('huge.jpg', 6144, 'image/jpeg'); // 6 MB

    Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('openBlank')
        ->set('title_en', 'Big File Test')
        ->set('type', 'photo')
        ->set('newPhotos', [$big])
        ->call('save')
        ->assertHasErrors(['newPhotos.*']);
});

it('rejects a non-image file', function () {
    $pdf = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');

    // The Livewire validation runs before save() touches any storage.
    // We set the files then immediately validate without calling save().
    $component = Volt::actingAs($this->user)
        ->test('admin.manage-gallery')
        ->call('openBlank')
        ->set('title_en', 'Non-image Test')
        ->set('type', 'photo')
        ->set('newPhotos', [$pdf]);

    // Trigger validation directly without rendering the full save response.
    $component->call('save');

    $component->assertHasErrors(['newPhotos.*']);
});

// ---------------------------------------------------------------------------
// Public tests
// ---------------------------------------------------------------------------

it('renders the gallery page in English with the album badge for a multi-photo item', function () {
    GalleryItem::create([
        'title' => ['en' => 'Multi Photo Album EN', 'fr' => 'Album multi photos FR'],
        'type' => 'photo',
        'photo_path' => 'gallery/cover.jpg',
        'photos' => ['gallery/cover.jpg', 'gallery/two.jpg', 'gallery/three.jpg'],
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->get('/gallery');
    $response->assertStatus(200);
    $response->assertSee('Multi Photo Album EN');
    // Badge shows the total photo count with trans_choice (+3 photos)
    $response->assertSee('+3 photos');
});

it('renders the gallery page in French with the album badge', function () {
    GalleryItem::create([
        'title' => ['en' => 'FR Album EN', 'fr' => 'Album FR'],
        'type' => 'photo',
        'photo_path' => 'gallery/fr_cover.jpg',
        'photos' => ['gallery/fr_cover.jpg', 'gallery/fr_two.jpg'],
        'sort_order' => 0,
        'is_active' => true,
    ]);

    // Switch locale via session (this app uses session-based locale).
    $response = $this->withSession(['locale' => 'fr'])->get('/gallery');
    $response->assertStatus(200);
    $response->assertSee('Album FR');
    $response->assertSee('+2 photos');
});

it('renders a single-photo item with no album badge', function () {
    GalleryItem::create([
        'title' => ['en' => 'Single Photo Item'],
        'type' => 'photo',
        'photo_path' => 'gallery/single.jpg',
        'photos' => null,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->get('/gallery');
    $response->assertStatus(200);
    $response->assertSee('Single Photo Item');
    // No album badge
    $response->assertDontSee('lightbox', false);
});
