<?php

use App\Models\Event;
use App\Services\DeepLTranslator;
use Carbon\Carbon;
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

    public $tag_en = '';

    public $tag_fr = '';

    public $event_date = '';

    public $date_label_en = '';

    public $date_label_fr = '';

    public $location_en = '';

    public $location_fr = '';

    public $description_en = '';

    public $description_fr = '';

    public $cta_text_en = '';

    public $cta_text_fr = '';

    public $thumbnail;

    public $thumbnail_path;

    public $speakers = [];

    public $speakerUploads = [];

    public $sort_order = 0;

    public $is_active = true;

    public $autoOn = true;

    public $touched = [];

    public $translateNote = '';

    protected array $translatable = ['title', 'tag', 'date_label', 'location', 'description', 'cta_text'];

    public function rules()
    {
        return [
            'title_en' => ['required', 'min:3'],
            'title_fr' => ['nullable', 'min:3'],
            'tag_en' => ['nullable', 'max:60'],
            'tag_fr' => ['nullable', 'max:60'],
            'event_date' => ['required', 'date'],
            'date_label_en' => ['nullable', 'max:120'],
            'date_label_fr' => ['nullable', 'max:120'],
            'location_en' => ['nullable', 'max:120'],
            'location_fr' => ['nullable', 'max:120'],
            'description_en' => ['nullable', 'min:10'],
            'description_fr' => ['nullable', 'min:10'],
            'cta_text_en' => ['nullable', 'max:60'],
            'cta_text_fr' => ['nullable', 'max:60'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'speakerUploads.*' => ['nullable', 'image', 'max:5120'],
            'speakers.*.name' => ['nullable', 'max:120'],
            'speakers.*.title_en' => ['nullable', 'max:160'],
            'speakers.*.title_fr' => ['nullable', 'max:160'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = Event::orderBy('event_date', 'desc')->get()
            ->map(function ($event) {
                $event->computed_status = Carbon::parse($event->event_date)->isFuture() ? 'Upcoming' : 'Past';

                return $event;
            });
    }

    public function openBlank()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function addSpeaker()
    {
        $this->speakers[] = ['name' => '', 'title_en' => '', 'title_fr' => '', 'image' => null];
        $index = count($this->speakers) - 1;
        $this->touched["speaker.{$index}"] = false;
        $this->speakerUploads[$index] = null;
    }

    public function removeSpeaker($index)
    {
        $speakerTouched = [];
        $speakerUploads = [];
        foreach ($this->speakers as $i => $s) {
            $speakerTouched[] = $this->touched["speaker.{$i}"] ?? false;
            $speakerUploads[] = $this->speakerUploads[$i] ?? null;
        }

        unset($this->speakers[$index]);
        unset($speakerTouched[$index]);
        unset($speakerUploads[$index]);

        $this->speakers = array_values($this->speakers);
        $speakerTouched = array_values($speakerTouched);
        $this->speakerUploads = array_values($speakerUploads);

        $this->touched = array_filter(
            $this->touched,
            fn ($k) => ! str_starts_with($k, 'speaker.'),
            ARRAY_FILTER_USE_KEY
        );

        foreach ($speakerTouched as $i => $t) {
            $this->touched["speaker.{$i}"] = $t;
        }
    }

    public function removeSpeakerImage($index)
    {
        if (isset($this->speakers[$index])) {
            $this->speakers[$index]['image'] = null;
        }
        $this->speakerUploads[$index] = null;
    }

    public function edit($id)
    {
        $item = Event::findOrFail($id);
        $this->editingId = $id;
        $this->title_en = $item->getTranslation('title', 'en', false);
        $this->title_fr = $item->getTranslation('title', 'fr', false);
        $this->tag_en = $item->getTranslation('tag', 'en', false);
        $this->tag_fr = $item->getTranslation('tag', 'fr', false);
        $this->event_date = $item->event_date ? Carbon::parse($item->event_date)->format('Y-m-d') : '';
        $this->date_label_en = $item->getTranslation('date_label', 'en', false);
        $this->date_label_fr = $item->getTranslation('date_label', 'fr', false);
        $this->location_en = $item->getTranslation('location', 'en', false);
        $this->location_fr = $item->getTranslation('location', 'fr', false);
        $this->description_en = $item->getTranslation('description', 'en', false);
        $this->description_fr = $item->getTranslation('description', 'fr', false);
        $this->cta_text_en = $item->getTranslation('cta_text', 'en', false);
        $this->cta_text_fr = $item->getTranslation('cta_text', 'fr', false);
        $this->thumbnail_path = $item->thumbnail;

        $rawSpeakers = $item->speakers ?? [];
        $speakers = [];
        $this->speakerUploads = [];
        foreach ($rawSpeakers as $speaker) {
            $rawTitle = $speaker['title'] ?? '';
            $title_en = '';
            $title_fr = '';
            if (is_array($rawTitle)) {
                $title_en = $rawTitle['en'] ?? '';
                $title_fr = $rawTitle['fr'] ?? '';
            } elseif (is_string($rawTitle)) {
                $title_en = $rawTitle;
                $title_fr = '';
            }
            $speakers[] = [
                'name' => $speaker['name'] ?? '',
                'title_en' => $title_en,
                'title_fr' => $title_fr,
                'image' => $speaker['image'] ?? null,
            ];
        }
        $this->speakers = $speakers;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;

        $this->touched = [
            'title' => filled($this->title_fr),
            'tag' => filled($this->tag_fr),
            'date_label' => filled($this->date_label_fr),
            'location' => filled($this->location_fr),
            'description' => filled($this->description_fr),
            'cta_text' => filled($this->cta_text_fr),
        ];
        foreach ($this->speakers as $index => $sp) {
            $this->touched["speaker.{$index}"] = filled($sp['title_fr'] ?? '');
        }
        $this->translateNote = '';
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        $cleanSpeakers = collect($this->speakers)
            ->filter(fn ($s) => filled($s['name'] ?? null))
            ->map(function ($s, $index) {
                $title = array_filter([
                    'en' => $s['title_en'] ?? null,
                    'fr' => $s['title_fr'] ?? null,
                ], 'filled');

                $imagePath = $s['image'] ?? null;
                if (isset($this->speakerUploads[$index]) && $this->speakerUploads[$index]) {
                    $imagePath = $this->speakerUploads[$index]->store('speakers', 'public');
                }

                return [
                    'name' => $s['name'],
                    'title' => $title ?: null,
                    'image' => $imagePath,
                ];
            })
            ->values()
            ->toArray();

        $data = [
            'title' => array_filter(['en' => $this->title_en, 'fr' => $this->title_fr], 'filled'),
            'tag' => array_filter(['en' => $this->tag_en, 'fr' => $this->tag_fr], 'filled'),
            'event_date' => $this->event_date,
            'date_label' => array_filter(['en' => $this->date_label_en, 'fr' => $this->date_label_fr], 'filled'),
            'location' => array_filter(['en' => $this->location_en, 'fr' => $this->location_fr], 'filled'),
            'description' => array_filter(['en' => $this->description_en, 'fr' => $this->description_fr], 'filled'),
            'cta_text' => array_filter(['en' => $this->cta_text_en, 'fr' => $this->cta_text_fr], 'filled'),
            'speakers' => $cleanSpeakers ?: null,
            'is_upcoming' => Carbon::parse($this->event_date)->isFuture(),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->thumbnail) {
            $data['thumbnail'] = $this->thumbnail->store('events', 'public');
        }

        if ($this->editingId) {
            $item = Event::findOrFail($this->editingId);
            if ($this->thumbnail && $item->thumbnail) {
                Storage::disk('public')->delete($item->thumbnail);
            }
            if (blank($this->title_fr)) {
                $item->forgetTranslation('title', 'fr');
            }
            if (blank($this->tag_fr)) {
                $item->forgetTranslation('tag', 'fr');
            }
            if (blank($this->date_label_fr)) {
                $item->forgetTranslation('date_label', 'fr');
            }
            if (blank($this->location_fr)) {
                $item->forgetTranslation('location', 'fr');
            }
            if (blank($this->description_fr)) {
                $item->forgetTranslation('description', 'fr');
            }
            if (blank($this->cta_text_fr)) {
                $item->forgetTranslation('cta_text', 'fr');
            }
            $item->update($data);
        } else {
            Event::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Event saved.');
    }

    protected function runTranslate(string $field): void
    {
        $isSpeaker = str_starts_with($field, 'speaker.');
        if ($isSpeaker) {
            $index = (int) substr($field, 8);
            $english = trim((string) ($this->speakers[$index]['title_en'] ?? ''));
        } else {
            $english = trim((string) $this->{$field.'_en'});
        }

        if (! $this->autoOn || ($this->touched[$field] ?? false) || mb_strlen($english) < 3) {
            return;
        }

        $allowed = RateLimiter::attempt('deepl:'.auth()->id(), 150, fn () => true, 60);
        if (! $allowed) {
            $this->translateNote = 'Too many automatic translations. Wait a minute or type the French yourself.';

            return;
        }

        if ($isSpeaker) {
            $result = app(DeepLTranslator::class)->translate(['speaker_title' => $english]);

            if ($result === null || ! isset($result['speaker_title'])) {
                $this->translateNote = 'Automatic translation is unavailable right now. You can type the French yourself.';

                return;
            }

            $this->speakers[$index]['title_fr'] = $result['speaker_title'];
            $this->translateNote = '';
        } else {
            $result = app(DeepLTranslator::class)->translate([$field => $english]);

            if ($result === null || ! isset($result[$field])) {
                $this->translateNote = 'Automatic translation is unavailable right now. You can type the French yourself.';

                return;
            }

            $this->{$field.'_fr'} = $result[$field];
            $this->translateNote = '';
        }
    }

    public function updated($name, $value)
    {
        if (preg_match('/^speakers\.(\d+)\.title_(en|fr)$/', $name, $matches)) {
            $index = (int) $matches[1];
            $lang = $matches[2];
            $field = "speaker.{$index}";

            if ($lang === 'en') {
                $this->runTranslate($field);
            } else {
                $this->touched[$field] = filled($value);
            }

            return;
        }

        if (str_ends_with($name, '_en')) {
            $base = substr($name, 0, -3);
            if (in_array($base, $this->translatable, true)) {
                $this->runTranslate($base);
            }
        } elseif (str_ends_with($name, '_fr')) {
            $base = substr($name, 0, -3);
            if (in_array($base, $this->translatable, true)) {
                $this->touched[$base] = filled($value);
            }
        }
    }

    public function delete($id)
    {
        $item = Event::findOrFail($id);
        if ($item->thumbnail) {
            Storage::disk('public')->delete($item->thumbnail);
        }
        $item->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Event deleted.');
    }

    public function resetForm()
    {
        $this->reset([
            'title_en', 'title_fr',
            'tag_en', 'tag_fr',
            'event_date',
            'date_label_en', 'date_label_fr',
            'location_en', 'location_fr',
            'description_en', 'description_fr',
            'cta_text_en', 'cta_text_fr',
            'thumbnail', 'thumbnail_path',
            'speakers', 'speakerUploads',
            'sort_order', 'is_active',
            'isEditing', 'editingId',
            'touched', 'translateNote',
        ]);
        $this->is_active = true;
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} event{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : 'openBlank' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Event' }}
        </button>
    </div>

    @if ($isEditing)
    <div class="card" style="margin-bottom:24px">
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Title (English)</label>
                        <input type="text" wire:model.live.debounce.300ms="title_en" class="input @error('title_en') is-invalid @enderror">
                        @error('title_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Titre (Français)</label>
                        <input type="text" wire:model.live.blur="title_fr" class="input @error('title_fr') is-invalid @enderror">
                        @error('title_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:14px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Tag (English, e.g. Launch)</label>
                        <input type="text" wire:model.live.debounce.300ms="tag_en" class="input @error('tag_en') is-invalid @enderror">
                        @error('tag_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Tag (Français, ex. Lancement)</label>
                        <input type="text" wire:model.live.blur="tag_fr" class="input @error('tag_fr') is-invalid @enderror">
                        @error('tag_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:14px">
                <div class="field" style="margin-top:0">
                    <label>Event Date</label>
                    <input type="date" wire:model="event_date" class="input @error('event_date') is-invalid @enderror">
                    @error('event_date') <p class="field-error">{{ $message }}</p> @enderror
                    @if ($event_date)
                        <p style="margin-top:6px;font-size:.78rem;color:var(--muted)">
                            Will show as: <strong>{{ \Carbon\Carbon::parse($event_date)->isFuture() ? 'Upcoming' : 'Past' }}</strong> (computed automatically)
                        </p>
                    @endif
                </div>
                <div></div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:14px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Date Label (English, optional)</label>
                        <input type="text" wire:model.live.debounce.300ms="date_label_en" placeholder="August 25, 2026" class="input @error('date_label_en') is-invalid @enderror">
                        @error('date_label_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Location (English)</label>
                        <input type="text" wire:model.live.debounce.300ms="location_en" placeholder="Virtual" class="input @error('location_en') is-invalid @enderror">
                        @error('location_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Description (English)</label>
                        <textarea wire:model.live.debounce.300ms="description_en" rows="4" class="input @error('description_en') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('description_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>CTA Button Text (English, optional)</label>
                        <input type="text" wire:model.live.debounce.300ms="cta_text_en" placeholder="Learn More" class="input @error('cta_text_en') is-invalid @enderror">
                        @error('cta_text_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Libellé de date (Français, optionnel)</label>
                        <input type="text" wire:model.live.blur="date_label_fr" placeholder="25 août 2026" class="input @error('date_label_fr') is-invalid @enderror">
                        @error('date_label_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Lieu (Français)</label>
                        <input type="text" wire:model.live.blur="location_fr" placeholder="En ligne" class="input @error('location_fr') is-invalid @enderror">
                        @error('location_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Description (Français)</label>
                        <textarea wire:model.live.blur="description_fr" rows="4" class="input @error('description_fr') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('description_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Texte du bouton CTA (Français, optionnel)</label>
                        <input type="text" wire:model.live.blur="cta_text_fr" placeholder="En savoir plus" class="input @error('cta_text_fr') is-invalid @enderror">
                        @error('cta_text_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                <label class="check" style="margin-top:0">
                    <input type="checkbox" wire:model="autoOn">
                    Auto-translate English to French as I type (draft, review before saving)
                </label>
                <span wire:loading wire:target="title_en,tag_en,date_label_en,location_en,description_en,cta_text_en,speakers" style="color:var(--muted);font-size:.8rem">Translating...</span>
            </div>
            @if ($translateNote)
                <p class="field-error" style="margin-top:8px">{{ $translateNote }}</p>
            @endif
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

            <div class="field" style="margin-top:18px">
                <label>Thumbnail</label>
                <input type="file" wire:model="thumbnail" accept="image/*" class="input @error('thumbnail') is-invalid @enderror">
                @error('thumbnail') <p class="field-error">{{ $message }}</p> @enderror
                <div wire:loading wire:target="thumbnail" style="margin-top:8px;font-size:.82rem;color:var(--muted)">Uploading...</div>

                @if ($thumbnail)
                    <img src="{{ $thumbnail->temporaryUrl() }}" style="margin-top:10px;height:80px;border-radius:8px;border:1px solid var(--line)">
                @elseif ($thumbnail_path)
                    <div style="margin-top:10px;display:flex;align-items:center;gap:10px">
                        <img src="{{ Storage::url($thumbnail_path) }}" style="height:80px;border-radius:8px;border:1px solid var(--line)">
                        <span style="font-size:.8rem;color:var(--muted)">Current thumbnail (choose a file to replace)</span>
                    </div>
                @endif
            </div>

            <div class="field">
                <label>Keynote Speakers (optional)</label>
                <p style="color:var(--muted);font-size:.8rem;margin-bottom:10px">Add keynote speakers with their name, title, and photo.</p>
                @foreach ($speakers as $index => $speaker)
                    <div style="background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:12px 14px;margin-top:10px">
                        <div style="display:flex;gap:14px;align-items:flex-start">
                            {{-- Speaker Photo / Upload --}}
                            <div style="flex-shrink:0;text-align:center">
                                @php
                                    $previewUrl = null;
                                    if (isset($speakerUploads[$index]) && $speakerUploads[$index]) {
                                        try { $previewUrl = $speakerUploads[$index]->temporaryUrl(); } catch (\Throwable $e) {}
                                    } elseif (!empty($speaker['image'])) {
                                        $previewUrl = Storage::url($speaker['image']);
                                    }
                                @endphp
                                <div style="width:52px;height:52px;border-radius:50%;background:var(--paper-dim);border:2px solid var(--line);display:flex;align-items:center;justify-content:center;overflow:hidden;margin:0 auto">
                                    @if ($previewUrl)
                                        <img src="{{ $previewUrl }}" style="width:100%;height:100%;object-fit:cover">
                                    @else
                                        <span style="font-size:1.2rem;color:var(--muted)">👤</span>
                                    @endif
                                </div>
                                <label style="display:inline-block;margin-top:4px;font-size:.72rem;color:var(--clay);cursor:pointer;font-weight:600">
                                    {{ $previewUrl ? 'Change' : '+ Photo' }}
                                    <input type="file" wire:model="speakerUploads.{{ $index }}" accept="image/*" style="display:none">
                                </label>
                                @if ($previewUrl)
                                    <div>
                                        <button type="button" wire:click="removeSpeakerImage({{ $index }})" style="background:none;border:none;color:var(--danger);font-size:.68rem;cursor:pointer;padding:0">Remove</button>
                                    </div>
                                @endif
                                <div wire:loading wire:target="speakerUploads.{{ $index }}" style="font-size:.68rem;color:var(--muted)">Uploading...</div>
                            </div>

                            {{-- Speaker Details Inputs --}}
                            <div style="flex:1;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px">
                                <div>
                                    <input type="text" wire:model="speakers.{{ $index }}.name" placeholder="Name" class="input" style="width:100%">
                                    @error("speakers.{$index}.name") <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <input type="text" wire:model.live.debounce.300ms="speakers.{{ $index }}.title_en" placeholder="Title (English)" class="input" style="width:100%">
                                </div>
                                <div>
                                    <input type="text" wire:model.live.blur="speakers.{{ $index }}.title_fr" placeholder="Titre (Français)" class="input" style="width:100%">
                                </div>
                            </div>

                            {{-- Remove Speaker Button --}}
                            <div>
                                <button type="button" wire:click="removeSpeaker({{ $index }})" class="btn-ghost" style="padding:8px 12px;color:var(--danger);border-color:#F3C9C6;font-size:.8rem">Remove</button>
                            </div>
                        </div>
                    </div>
                @endforeach
                <button type="button" wire:click="addSpeaker" class="btn-ghost" style="margin-top:12px">+ Add Speaker</button>
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
                <button type="submit" class="btn-primary" style="width:auto;margin-top:0">
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
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">TITLE</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">DATE</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">STATUS</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">SPEAKERS</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">FRENCH</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:14px 16px;font-weight:500">{{ $item->getTranslation('title', 'en', false) }}</td>
                    <td style="padding:14px 16px;color:var(--muted)">{{ $item->getTranslation('date_label', 'en', false) ?: ($item->event_date ? \Carbon\Carbon::parse($item->event_date)->format('M j, Y') : '') }}</td>
                    <td style="padding:14px 16px">
                        <span style="font-size:.82rem;color:{{ $item->computed_status === 'Upcoming' ? 'var(--ok)' : 'var(--muted)' }}">{{ $item->computed_status }}</span>
                    </td>
                    <td style="padding:14px 16px;color:var(--muted);font-size:.85rem">{{ count($item->speakers ?? []) }}</td>
                    <td style="padding:14px 16px;font-size:.82rem">
                        @if ($item->hasTranslation('title', 'fr') && $item->hasTranslation('tag', 'fr') && $item->hasTranslation('description', 'fr') && $item->hasTranslation('date_label', 'fr') && $item->hasTranslation('location', 'fr') && $item->hasTranslation('cta_text', 'fr'))
                            <span style="color:var(--ok)">FR done</span>
                        @else
                            <span style="color:var(--muted)">FR missing</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;text-align:right;white-space:nowrap">
                        <button wire:click="edit({{ $item->id }})" class="btn-ghost" style="padding:6px 12px;font-size:.8rem">Edit</button>
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this event?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:24px 16px;text-align:center;color:var(--muted)">No events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>