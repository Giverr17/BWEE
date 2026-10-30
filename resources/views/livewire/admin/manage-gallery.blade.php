<?php

use App\Models\Event;
use App\Models\GalleryItem;
use App\Services\DeepLTranslator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $items;

    public $isEditing = false;

    public $editingId = null;

    public $title_en = '';

    public $title_fr = '';

    public $caption_en = '';

    public $caption_fr = '';

    public $type = 'photo';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public $newPhotos = [];

    /** Ordered array of stored paths already on this item. */
    public $existingPhotos = [];

    /** Paths queued for deletion on save (only after confirming no other record uses them). */
    public $photosToRemove = [];

    /** Legacy single-photo path for items created before albums. */
    public $photo_path;

    public $video_url_input = '';

    public $youtube_id;

    public $event_id = '';

    public $sort_order = 0;

    public $is_active = true;

    public $autoOn = true;

    public $touched = [];

    public $translateNote = '';

    protected array $translatable = ['title', 'caption'];

    public function rules()
    {
        $rules = [
            'title_en' => ['required', 'min:3'],
            'title_fr' => ['nullable', 'min:3'],
            'caption_en' => ['nullable', 'max:500'],
            'caption_fr' => ['nullable', 'max:500'],
            'type' => ['required', 'in:photo,video'],
            'event_id' => ['nullable', 'exists:events,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'newPhotos.*' => ['image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ];

        if ($this->type === 'photo') {
            $hasExisting = ! empty($this->existingPhotos) || $this->photo_path;
            if (! $this->editingId && ! $hasExisting) {
                $rules['newPhotos'] = ['required', 'array', 'min:1', 'max:20'];
            } else {
                $rules['newPhotos'] = ['nullable', 'array', 'max:20'];
            }
        } else {
            $rules['video_url_input'] = $this->youtube_id ? ['nullable'] : ['required'];
        }

        return $rules;
    }

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = GalleryItem::with('event')->orderBy('sort_order')->get();
    }

    public function openBlank()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    private function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', trim($url))) {
            return trim($url);
        }

        return null;
    }

    public function edit($id)
    {
        $item = GalleryItem::findOrFail($id);
        $this->editingId = $id;
        $this->title_en = $item->getTranslation('title', 'en', false);
        $this->title_fr = $item->getTranslation('title', 'fr', false);
        $this->caption_en = $item->getTranslation('caption', 'en', false);
        $this->caption_fr = $item->getTranslation('caption', 'fr', false);
        $this->type = $item->type;
        $this->photo_path = $item->photo_path;
        $this->existingPhotos = $item->photos ?? [];
        $this->photosToRemove = [];
        $this->youtube_id = $item->youtube_id;
        $this->video_url_input = $item->youtube_id ? "https://youtu.be/{$item->youtube_id}" : '';
        $this->event_id = $item->event_id;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
        $this->touched = [
            'title' => filled($this->title_fr),
            'caption' => filled($this->caption_fr),
        ];
        $this->translateNote = '';
        $this->isEditing = true;
    }

    /** Move a photo in existingPhotos to position 0 (make it the cover). */
    public function makeCover(int $index): void
    {
        if (! isset($this->existingPhotos[$index])) {
            return;
        }

        $photos = $this->existingPhotos;
        $cover = array_splice($photos, $index, 1);
        array_unshift($photos, $cover[0]);
        $this->existingPhotos = array_values($photos);
    }

    /** Move a photo one position to the left. */
    public function moveLeft(int $index): void
    {
        if ($index <= 0 || ! isset($this->existingPhotos[$index])) {
            return;
        }

        $photos = $this->existingPhotos;
        [$photos[$index - 1], $photos[$index]] = [$photos[$index], $photos[$index - 1]];
        $this->existingPhotos = array_values($photos);
    }

    /** Move a photo one position to the right. */
    public function moveRight(int $index): void
    {
        if ($index >= count($this->existingPhotos) - 1) {
            return;
        }

        $photos = $this->existingPhotos;
        [$photos[$index], $photos[$index + 1]] = [$photos[$index + 1], $photos[$index]];
        $this->existingPhotos = array_values($photos);
    }

    /** Queue an existing photo for removal on save. */
    public function removeExistingPhoto(int $index): void
    {
        if (! isset($this->existingPhotos[$index])) {
            return;
        }

        $path = $this->existingPhotos[$index];
        $this->photosToRemove[] = $path;
        $photos = $this->existingPhotos;
        array_splice($photos, $index, 1);
        $this->existingPhotos = array_values($photos);
    }

    public function save()
    {
        if ($this->type === 'video' && $this->video_url_input && ! $this->youtube_id) {
            $this->addError('video_url_input', 'Could not read a YouTube video ID from that link. Check the URL and try again.');

            return;
        }

        $this->validate();

        // Store newly uploaded photos.
        $newPaths = [];
        foreach ($this->newPhotos as $file) {
            $newPaths[] = $file->store('gallery', 'public');
        }

        // Build final ordered photo list.
        $allPhotos = array_merge($this->existingPhotos, $newPaths);

        // Determine cover path.
        $coverPath = ! empty($allPhotos) ? $allPhotos[0] : $this->photo_path;

        $data = [
            'title' => array_filter(['en' => $this->title_en, 'fr' => $this->title_fr], 'filled'),
            'caption' => array_filter(['en' => $this->caption_en, 'fr' => $this->caption_fr], 'filled'),
            'type' => $this->type,
            'event_id' => $this->event_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'youtube_id' => $this->type === 'video' ? $this->youtube_id : null,
        ];

        if ($this->type === 'photo') {
            $data['photos'] = ! empty($allPhotos) ? $allPhotos : null;
            $data['photo_path'] = $coverPath ?: null;
        }

        if ($this->editingId) {
            $item = GalleryItem::findOrFail($this->editingId);
            if ($this->type === 'video') {
                $data['photo_path'] = null;
                $data['photos'] = null;
            }
            if (blank($this->title_fr)) {
                $item->forgetTranslation('title', 'fr');
            }
            if (blank($this->caption_fr)) {
                $item->forgetTranslation('caption', 'fr');
            }
            $item->update($data);

            // Delete removed photos if no other record uses the path.
            foreach ($this->photosToRemove as $path) {
                $usedByOther = GalleryItem::where('id', '!=', $this->editingId)
                    ->where(function ($q) use ($path) {
                        $q->where('photo_path', $path)
                            ->orWhereJsonContains('photos', $path);
                    })
                    ->exists();

                if (! $usedByOther) {
                    Storage::disk('public')->delete($path);
                }
            }
        } else {
            GalleryItem::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Gallery item saved.');
    }

    protected function runTranslate(string $field): void
    {
        try {
            $english = trim((string) $this->{$field.'_en'});

            if (! $this->autoOn || ($this->touched[$field] ?? false) || mb_strlen($english) < 3) {
                return;
            }

            $allowed = RateLimiter::attempt('deepl:'.auth()->id(), 150, fn () => true, 60);
            if (! $allowed) {
                $this->translateNote = 'Too many translations, try again in a moment';

                return;
            }

            $result = app(DeepLTranslator::class)->translate([$field => $english]);

            if ($result === null || ! isset($result[$field])) {
                $this->translateNote = 'Automatic translation is unavailable right now. You can type the French yourself.';

                return;
            }

            $this->{$field.'_fr'} = $result[$field];
            $this->translateNote = '';
        } catch (\Throwable $e) {
            $this->translateNote = 'Automatic translation is unavailable right now. You can type the French yourself.';
        }
    }

    public function updated($name, $value)
    {
        if ($name === 'video_url_input') {
            $this->youtube_id = $value ? $this->extractYoutubeId($value) : null;

            return;
        }

        if (str_ends_with($name, '_fr')) {
            $base = substr($name, 0, -3);
            if (in_array($base, $this->translatable, true)) {
                $this->touched[$base] = filled($value);
            }
        }

        if (app()->runningUnitTests()) {
            if (str_ends_with($name, '_en')) {
                $base = substr($name, 0, -3);
                if (in_array($base, $this->translatable, true)) {
                    $this->runTranslate($base);
                }
            }
        }
    }

    public function delete($id)
    {
        $item = GalleryItem::findOrFail($id);

        // Delete all stored photos.
        $paths = array_filter(array_merge(
            $item->photos ?? [],
            $item->photo_path ? [$item->photo_path] : [],
        ));

        foreach (array_unique($paths) as $path) {
            $usedByOther = GalleryItem::where('id', '!=', $id)
                ->where(function ($q) use ($path) {
                    $q->where('photo_path', $path)
                        ->orWhereJsonContains('photos', $path);
                })
                ->exists();

            if (! $usedByOther) {
                Storage::disk('public')->delete($path);
            }
        }

        $item->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Gallery item deleted.');
    }

    public function resetForm()
    {
        $this->reset([
            'title_en', 'title_fr', 'caption_en', 'caption_fr',
            'type', 'newPhotos', 'existingPhotos', 'photosToRemove',
            'photo_path', 'video_url_input', 'youtube_id',
            'event_id', 'sort_order', 'is_active',
            'isEditing', 'editingId', 'touched', 'translateNote',
        ]);
        $this->type = 'photo';
        $this->is_active = true;
    }

    public function with()
    {
        return ['events' => Event::orderBy('event_date', 'desc')->get()];
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} item{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : 'openBlank' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Gallery Item' }}
        </button>
    </div>

    @if ($isEditing)
    <div class="card" style="margin-bottom:24px"
         x-data="adminForm({
             formKey: 'gallery',
             recordId: @entangle('editingId'),
             pairs: [
                 { en: 'title_en', fr: 'title_fr', base: 'title' },
                 { en: 'caption_en', fr: 'caption_fr', base: 'caption' }
             ]
         })">
        @include('admin.draft-restore-bar')
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Title (English)</label>
                        <input type="text" wire:model.live.debounce.300ms="title_en" class="input @error('title_en') is-invalid @enderror">
                        @error('title_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Caption (English, optional)</label>
                        <textarea wire:model.live.debounce.300ms="caption_en" rows="3" class="input @error('caption_en') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('caption_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Titre (Français)</label>
                        <input type="text" wire:model.live.blur="title_fr" class="input @error('title_fr') is-invalid @enderror">
                        @error('title_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Légende (Français, optionnel)</label>
                        <textarea wire:model.live.blur="caption_fr" rows="3" class="input @error('caption_fr') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('caption_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                <label class="check" style="margin-top:0">
                    <input type="checkbox" x-model="autoOn" wire:model="autoOn">
                    Auto-translate English to French as I type (draft, review before saving)
                </label>
                <span x-show="translateStatus === 'translating'" style="color:var(--muted);font-size:.8rem;display:none">Translating...</span>
                <span x-show="translateNote" x-text="translateNote" class="field-error" style="font-size:.8rem;display:none"></span>
            </div>
            @if ($translateNote)
                <p class="field-error" style="margin-top:8px">{{ $translateNote }}</p>
            @endif
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

            <div class="field" style="margin-top:18px">
                <label>Type</label>
                <div style="display:flex;gap:16px">
                    <label class="check" style="margin-top:0">
                        <input type="radio" wire:model.live="type" value="photo"> Photo
                    </label>
                    <label class="check" style="margin-top:0">
                        <input type="radio" wire:model.live="type" value="video"> Video
                    </label>
                </div>
            </div>

            @if ($type === 'photo')
                {{-- Current photos grid --}}
                @if (count($existingPhotos) > 0)
                    <div class="field">
                        <label>Current Photos ({{ count($existingPhotos) }})</label>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
                            @foreach ($existingPhotos as $idx => $path)
                                <div style="position:relative;display:flex;flex-direction:column;align-items:center;gap:4px">
                                    <div style="position:relative">
                                        <img src="{{ Storage::url($path) }}" loading="lazy"
                                             style="height:80px;width:110px;object-fit:cover;border-radius:6px;border:2px solid {{ $idx === 0 ? 'var(--gold)' : 'var(--line)' }}">
                                        @if ($idx === 0)
                                            <span style="position:absolute;top:3px;left:3px;background:var(--gold);color:var(--purple-deep);font-size:.6rem;font-weight:700;padding:1px 5px;border-radius:99px">Cover</span>
                                        @endif
                                    </div>
                                    <div style="display:flex;gap:3px">
                                        @if ($idx > 0)
                                            <button type="button" wire:click="makeCover({{ $idx }})"
                                                    title="Make cover"
                                                    style="background:var(--purple);color:#fff;border:none;border-radius:5px;padding:2px 6px;font-size:.68rem;cursor:pointer">Cover</button>
                                            <button type="button" wire:click="moveLeft({{ $idx }})"
                                                    title="Move left"
                                                    style="background:var(--paper);border:1px solid var(--line);border-radius:5px;padding:2px 6px;font-size:.68rem;cursor:pointer">&#8592;</button>
                                        @endif
                                        @if ($idx < count($existingPhotos) - 1)
                                            <button type="button" wire:click="moveRight({{ $idx }})"
                                                    title="Move right"
                                                    style="background:var(--paper);border:1px solid var(--line);border-radius:5px;padding:2px 6px;font-size:.68rem;cursor:pointer">&#8594;</button>
                                        @endif
                                        <button type="button" wire:click="removeExistingPhoto({{ $idx }})"
                                                title="Remove photo"
                                                style="background:var(--danger-bg);color:var(--danger);border:1px solid #F3C9C6;border-radius:5px;padding:2px 6px;font-size:.68rem;cursor:pointer">&#10005;</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p style="font-size:.78rem;color:var(--muted);margin-top:6px">Gold border = cover. Removed photos are deleted from storage when you save.</p>
                    </div>
                @elseif ($photo_path && count($existingPhotos) === 0)
                    {{-- Legacy single-photo --}}
                    <div class="field">
                        <label>Current Photo</label>
                        <img src="{{ Storage::url($photo_path) }}" loading="lazy" style="margin-top:8px;height:80px;border-radius:6px;border:1px solid var(--line)">
                    </div>
                @endif

                {{-- New photos upload --}}
                <div class="field">
                    <label>Add Photos (up to 20, jpg/jpeg/png/webp, max 5 MB each)</label>
                    <input type="file" wire:model="newPhotos" accept="image/jpeg,image/png,image/webp" multiple
                           class="input @error('newPhotos') is-invalid @enderror @error('newPhotos.*') is-invalid @enderror">
                    @error('newPhotos') <p class="field-error">{{ $message }}</p> @enderror
                    @error('newPhotos.*') <p class="field-error">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="newPhotos" style="margin-top:8px;font-size:.82rem;color:var(--muted)">Uploading...</div>

                    @if (count($newPhotos) > 0)
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
                            @foreach ($newPhotos as $preview)
                                @if ($preview)
                                    @php try { $previewUrl = $preview->temporaryUrl(); } catch (\Throwable $e) { $previewUrl = null; } @endphp
                                    @if ($previewUrl)
                                        <img src="{{ $previewUrl }}" style="height:70px;width:100px;object-fit:cover;border-radius:6px;border:1px solid var(--line)">
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="field">
                    <label>YouTube URL (any format, or paste just the video ID)</label>
                    <input type="text" wire:model.live.debounce.400ms="video_url_input" placeholder="https://youtu.be/..." class="input @error('video_url_input') is-invalid @enderror">
                    @error('video_url_input') <p class="field-error">{{ $message }}</p> @enderror

                    @if ($video_url_input && $youtube_id)
                        <div style="margin-top:12px">
                            <p style="font-size:.78rem;color:var(--ok);margin-bottom:8px">Video ID recognized: {{ $youtube_id }}</p>
                            <div style="aspect-ratio:16/9;max-width:320px;border-radius:8px;overflow:hidden">
                                <iframe src="https://www.youtube.com/embed/{{ $youtube_id }}" style="width:100%;height:100%;border:0" allowfullscreen></iframe>
                            </div>
                        </div>
                    @elseif ($video_url_input && !$youtube_id)
                        <p style="margin-top:8px;font-size:.78rem;color:var(--danger)">Could not read a video ID from this yet, keep typing or check the link.</p>
                    @endif
                </div>
            @endif

            <div class="field">
                <label>Link to Event (optional)</label>
                <select wire:model="event_id" class="input">
                    <option value="">None</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}">{{ $event->getTranslation('title', 'en', false) }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:20px;align-items:flex-end;margin-top:18px">
                <div class="field" style="margin-top:0">
                    <label>Display Order</label>
                    <input type="number" wire:model="sort_order" class="input" style="width:120px">
                </div>
                <label class="check" style="margin-top:0">
                    <input type="checkbox" wire:model="is_active">
                    Active (visible on site)
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:22px">
                <button type="submit" class="btn-primary" wire:offline.attr="disabled" style="width:auto;margin-top:0">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
                <button type="button" wire:click="resetForm" class="btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--paper);border-bottom:1px solid var(--line)">
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">PREVIEW</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">TITLE</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">TYPE</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">EVENT</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">FRENCH</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:10px 16px">
                        @if ($item->type === 'photo' && $item->cover_url)
                            <div style="position:relative;display:inline-block">
                                <img src="{{ $item->cover_url }}" loading="lazy" style="height:40px;width:60px;object-fit:cover;border-radius:4px">
                                @if ($item->photo_count > 1)
                                    <span style="position:absolute;bottom:2px;right:2px;background:rgba(0,0,0,.65);color:#fff;font-size:.58rem;font-weight:700;padding:1px 4px;border-radius:3px">{{ $item->photo_count }} photos</span>
                                @endif
                            </div>
                        @elseif ($item->type === 'video' && $item->youtube_id)
                            <img src="https://img.youtube.com/vi/{{ $item->youtube_id }}/default.jpg" style="height:40px;width:53px;object-fit:cover;border-radius:4px">
                        @else
                            <span style="font-size:.78rem;color:var(--muted);font-style:italic">Pending</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->getTranslation('title', 'en', false) }}</td>
                    <td style="padding:14px 16px;color:var(--muted);text-transform:capitalize">
                        {{ $item->type }}
                        @if ($item->type === 'photo' && $item->photo_count > 1)
                            <span style="font-size:.78rem;color:var(--muted)">({{ $item->photo_count }} photos)</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;color:var(--muted);font-size:.85rem">{{ $item->event?->getTranslation('title', 'en', false) ?? 'None' }}</td>
                    <td style="padding:14px 16px;font-size:.82rem">
                        @if ($item->hasTranslation('title', 'fr') && $item->hasTranslation('caption', 'fr'))
                            <span style="color:var(--ok)">FR done</span>
                        @else
                            <span style="color:var(--muted)">FR missing</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;text-align:right;white-space:nowrap">
                        <button wire:click="edit({{ $item->id }})" class="btn-ghost" style="padding:6px 12px;font-size:.8rem">Edit</button>
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this gallery item?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:24px 16px;text-align:center;color:var(--muted)">No gallery items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
