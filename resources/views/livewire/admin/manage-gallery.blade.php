<?php

use App\Models\Event;
use App\Models\GalleryItem;
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

    public $photo;

    public $photo_path;

    public $video_url_input = '';

    public $youtube_id;

    public $event_id = '';

    public $sort_order = 0;

    public $is_active = true;

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
        ];

        if ($this->type === 'photo') {
            $rules['photo'] = $this->editingId ? ['nullable', 'image', 'max:4096'] : ['required', 'image', 'max:4096'];
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

    public function updatedVideoUrlInput($value)
    {
        $this->youtube_id = $value ? $this->extractYoutubeId($value) : null;
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
        $this->youtube_id = $item->youtube_id;
        $this->video_url_input = $item->youtube_id ? "https://youtu.be/{$item->youtube_id}" : '';
        $this->event_id = $item->event_id;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
        $this->isEditing = true;
    }

    public function save()
    {
        if ($this->type === 'video' && $this->video_url_input && ! $this->youtube_id) {
            $this->addError('video_url_input', 'Could not read a YouTube video ID from that link. Check the URL and try again.');

            return;
        }

        $this->validate();

        $data = [
            'title' => array_filter(['en' => $this->title_en, 'fr' => $this->title_fr], 'filled'),
            'caption' => array_filter(['en' => $this->caption_en, 'fr' => $this->caption_fr], 'filled'),
            'type' => $this->type,
            'event_id' => $this->event_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'youtube_id' => $this->type === 'video' ? $this->youtube_id : null,
        ];

        if ($this->type === 'photo' && $this->photo) {
            $data['photo_path'] = $this->photo->store('gallery', 'public');
        }

        if ($this->editingId) {
            $item = GalleryItem::findOrFail($this->editingId);
            if ($this->type === 'photo' && $this->photo && $item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
            if ($this->type === 'video') {
                $data['photo_path'] = null;
            }
            if (blank($this->title_fr)) {
                $item->forgetTranslation('title', 'fr');
            }
            if (blank($this->caption_fr)) {
                $item->forgetTranslation('caption', 'fr');
            }
            $item->update($data);
        } else {
            GalleryItem::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Gallery item saved.');
    }

    public function delete($id)
    {
        $item = GalleryItem::findOrFail($id);
        if ($item->photo_path) {
            Storage::disk('public')->delete($item->photo_path);
        }
        $item->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Gallery item deleted.');
    }

    public function resetForm()
    {
        $this->reset(['title_en', 'title_fr', 'caption_en', 'caption_fr', 'type', 'photo', 'photo_path', 'video_url_input', 'youtube_id', 'event_id', 'sort_order', 'is_active', 'isEditing', 'editingId']);
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
        <button wire:click="{{ $isEditing ? 'resetForm' : '$set(\'isEditing\', true)' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Gallery Item' }}
        </button>
    </div>

    @if ($isEditing)
    <div class="card" style="margin-bottom:24px">
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Title (English)</label>
                        <input type="text" wire:model="title_en" class="input @error('title_en') is-invalid @enderror">
                        @error('title_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Caption (English, optional)</label>
                        <textarea wire:model="caption_en" rows="3" class="input @error('caption_en') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('caption_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Titre (Français)</label>
                        <input type="text" wire:model="title_fr" class="input @error('title_fr') is-invalid @enderror">
                        @error('title_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Légende (Français, optionnel)</label>
                        <textarea wire:model="caption_fr" rows="3" class="input @error('caption_fr') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('caption_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site.</p>

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
                <div class="field">
                    <label>Photo</label>
                    <input type="file" wire:model="photo" accept="image/*" class="input @error('photo') is-invalid @enderror">
                    @error('photo') <p class="field-error">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="photo" style="margin-top:8px;font-size:.82rem;color:var(--muted)">Uploading...</div>

                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" style="margin-top:10px;height:90px;border-radius:8px;border:1px solid var(--line)">
                    @elseif ($photo_path)
                        <img src="{{ Storage::url($photo_path) }}" style="margin-top:10px;height:90px;border-radius:8px;border:1px solid var(--line)">
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
                        @if ($item->type === 'photo' && $item->photo_path)
                            <img src="{{ Storage::url($item->photo_path) }}" style="height:40px;width:60px;object-fit:cover;border-radius:4px">
                        @elseif ($item->type === 'video' && $item->youtube_id)
                            <img src="https://img.youtube.com/vi/{{ $item->youtube_id }}/default.jpg" style="height:40px;width:53px;object-fit:cover;border-radius:4px">
                        @else
                            <span style="font-size:.78rem;color:var(--muted);font-style:italic">Pending</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->getTranslation('title', 'en', false) }}</td>
                    <td style="padding:14px 16px;color:var(--muted);text-transform:capitalize">{{ $item->type }}</td>
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