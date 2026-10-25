<?php

use App\Models\RoadmapItem;
use App\Services\DeepLTranslator;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;

new class extends Component
{
    public $items;

    public $isEditing = false;

    public $editingId = null;

    public $slug = '';

    public $title_en = '';

    public $title_fr = '';

    public $description_en = '';

    public $description_fr = '';

    public $icon = '';

    public $colour = '';

    public $sort_order = 0;

    public $is_active = true;

    public $autoOn = true;

    public $touched = [];

    public $translateNote = '';

    protected array $translatable = ['title', 'description'];

    public function rules()
    {
        return [
            'slug' => ['required', 'alpha_dash', 'max:60'],
            'title_en' => ['required', 'min:3'],
            'title_fr' => ['nullable', 'min:3'],
            'description_en' => ['required', 'min:10'],
            'description_fr' => ['nullable', 'min:10'],
            'icon' => ['nullable', 'max:60'],
            'colour' => ['nullable', 'max:20'],
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
        $this->items = RoadmapItem::orderBy('sort_order')->get();
    }

    public function openBlank()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $item = RoadmapItem::findOrFail($id);
        $this->editingId = $id;
        $this->slug = $item->slug;
        $this->title_en = $item->getTranslation('title', 'en', false);
        $this->title_fr = $item->getTranslation('title', 'fr', false);
        $this->description_en = $item->getTranslation('description', 'en', false);
        $this->description_fr = $item->getTranslation('description', 'fr', false);
        $this->icon = $item->icon;
        $this->colour = $item->colour;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
        $this->touched = [
            'title' => filled($this->title_fr),
            'description' => filled($this->description_fr),
        ];
        $this->translateNote = '';
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'slug' => $this->slug,
            'title' => array_filter(['en' => $this->title_en, 'fr' => $this->title_fr], 'filled'),
            'description' => array_filter(['en' => $this->description_en, 'fr' => $this->description_fr], 'filled'),
            'icon' => $this->icon ?: null,
            'colour' => $this->colour ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $item = RoadmapItem::findOrFail($this->editingId);
            if (blank($this->title_fr)) {
                $item->forgetTranslation('title', 'fr');
            }
            if (blank($this->description_fr)) {
                $item->forgetTranslation('description', 'fr');
            }
            $item->update($data);
        } else {
            RoadmapItem::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Roadmap item saved.');
    }

    protected function runTranslate(string $field): void
    {
        $english = trim((string) $this->{$field.'_en'});

        if (! $this->autoOn || ($this->touched[$field] ?? false) || mb_strlen($english) < 3) {
            return;
        }

        $allowed = RateLimiter::attempt('deepl:'.auth()->id(), 150, fn () => true, 60);
        if (! $allowed) {
            $this->translateNote = 'Too many automatic translations. Wait a minute or type the French yourself.';

            return;
        }

        $result = app(DeepLTranslator::class)->translate([$field => $english]);

        if ($result === null || ! isset($result[$field])) {
            $this->translateNote = 'Automatic translation is unavailable right now. You can type the French yourself.';

            return;
        }

        $this->{$field.'_fr'} = $result[$field];
        $this->translateNote = '';
    }

    public function updated($name, $value)
    {
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
        RoadmapItem::findOrFail($id)->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Roadmap item deleted.');
    }

    public function resetForm()
    {
        $this->reset(['slug', 'title_en', 'title_fr', 'description_en', 'description_fr', 'icon', 'colour', 'sort_order', 'is_active', 'isEditing', 'editingId', 'touched', 'translateNote']);
        $this->is_active = true;
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} item{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : 'openBlank' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Roadmap Item' }}
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
                    <div class="field">
                        <label>Description (English)</label>
                        <textarea wire:model.live.debounce.300ms="description_en" rows="4" class="input @error('description_en') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('description_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Titre (Français)</label>
                        <input type="text" wire:model.live.blur="title_fr" class="input @error('title_fr') is-invalid @enderror">
                        @error('title_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Description (Français)</label>
                        <textarea wire:model.live.blur="description_fr" rows="4" class="input @error('description_fr') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('description_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                <label class="check" style="margin-top:0">
                    <input type="checkbox" wire:model="autoOn">
                    Auto-translate English to French as I type (draft, review before saving)
                </label>
                <span wire:loading wire:target="title_en,description_en" style="color:var(--muted);font-size:.8rem">Translating...</span>
            </div>
            @if ($translateNote)
                <p class="field-error" style="margin-top:8px">{{ $translateNote }}</p>
            @endif
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:16px">
                <div class="field" style="margin-top:0">
                    <label>Slug</label>
                    <input type="text" wire:model="slug" class="input @error('slug') is-invalid @enderror">
                    @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field" style="margin-top:0">
                    <label>Icon (Material Symbol, optional)</label>
                    <input type="text" wire:model="icon" class="input @error('icon') is-invalid @enderror">
                    @error('icon') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field" style="margin-top:0">
                    <label>Accent Colour (optional)</label>
                    <input type="text" wire:model="colour" placeholder="#C9962C" class="input @error('colour') is-invalid @enderror">
                    @error('colour') <p class="field-error">{{ $message }}</p> @enderror
                </div>
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
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">ORDER</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">TITLE</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">FRENCH</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">STATUS</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:14px 16px;color:var(--muted)">{{ $item->sort_order }}</td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->getTranslation('title', 'en', false) }}</td>
                    <td style="padding:14px 16px;font-size:.82rem">
                        @if ($item->hasTranslation('title', 'fr') && $item->hasTranslation('description', 'fr'))
                            <span style="color:var(--ok)">FR done</span>
                        @else
                            <span style="color:var(--muted)">FR missing</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px">
                        @if ($item->is_active)
                            <span style="color:var(--ok);font-size:.82rem">Active</span>
                        @else
                            <span style="color:var(--muted);font-size:.82rem">Hidden</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;text-align:right;white-space:nowrap">
                        <button wire:click="edit({{ $item->id }})" class="btn-ghost" style="padding:6px 12px;font-size:.8rem">Edit</button>
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this roadmap item?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:24px 16px;text-align:center;color:var(--muted)">No roadmap items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>