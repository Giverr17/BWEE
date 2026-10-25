<?php

use App\Models\Partner;
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

    public $name = '';

    public $note_en = '';

    public $note_fr = '';

    public $url = '';

    public $logo;

    public $logo_path;

    public $sort_order = 0;

    public $is_active = true;

    public $autoOn = true;

    public $touched = [];

    public $translateNote = '';

    protected array $translatable = ['note'];

    public function rules()
    {
        return [
            'name' => ['required', 'min:2'],
            'note_en' => ['nullable', 'max:255'],
            'note_fr' => ['nullable', 'max:255'],
            'url' => ['nullable', 'url'],
            'logo' => ['nullable', 'image', 'max:2048'],
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
        $this->items = Partner::orderBy('sort_order')->get();
    }

    public function openBlank()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $item = Partner::findOrFail($id);
        $this->editingId = $id;
        $this->name = $item->name;
        $this->note_en = $item->getTranslation('note', 'en', false);
        $this->note_fr = $item->getTranslation('note', 'fr', false);
        $this->url = $item->url;
        $this->logo_path = $item->logo;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
        $this->touched = [
            'note' => filled($this->note_fr),
        ];
        $this->translateNote = '';
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'note' => array_filter(['en' => $this->note_en, 'fr' => $this->note_fr], 'filled'),
            'url' => $this->url ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->logo) {
            $data['logo'] = $this->logo->store('partners', 'public');
        }

        if ($this->editingId) {
            $item = Partner::findOrFail($this->editingId);
            if ($this->logo && $item->logo) {
                Storage::disk('public')->delete($item->logo);
            }
            if (blank($this->note_fr)) {
                $item->forgetTranslation('note', 'fr');
            }
            $item->update($data);
        } else {
            Partner::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Partner saved.');
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
        $item = Partner::findOrFail($id);
        if ($item->logo) {
            Storage::disk('public')->delete($item->logo);
        }
        $item->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Partner deleted.');
    }

    public function resetForm()
    {
        $this->reset(['name', 'note_en', 'note_fr', 'url', 'logo', 'logo_path', 'sort_order', 'is_active', 'isEditing', 'editingId', 'touched', 'translateNote']);
        $this->is_active = true;
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} partner{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : 'openBlank' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Partner' }}
        </button>
    </div>

    @if ($isEditing)
    <div class="card" style="margin-bottom:24px">
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div class="field" style="margin-top:0">
                    <label>Name</label>
                    <input type="text" wire:model="name" class="input @error('name') is-invalid @enderror">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field" style="margin-top:0">
                    <label>Website URL (optional)</label>
                    <input type="url" wire:model="url" placeholder="https://..." class="input @error('url') is-invalid @enderror">
                    @error('url') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:16px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Note (English)</label>
                        <input type="text" wire:model.live.debounce.300ms="note_en" placeholder="Launch partner" class="input @error('note_en') is-invalid @enderror">
                        @error('note_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Note (Français)</label>
                        <input type="text" wire:model.live.blur="note_fr" placeholder="Partenaire du lancement" class="input @error('note_fr') is-invalid @enderror">
                        @error('note_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                <label class="check" style="margin-top:0">
                    <input type="checkbox" wire:model="autoOn">
                    Auto-translate English to French as I type (draft, review before saving)
                </label>
                <span wire:loading wire:target="note_en" style="color:var(--muted);font-size:.8rem">Translating...</span>
            </div>
            @if ($translateNote)
                <p class="field-error" style="margin-top:8px">{{ $translateNote }}</p>
            @endif
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

            <div class="field" style="margin-top:18px">
                <label>Logo</label>
                <input type="file" wire:model="logo" accept="image/*" class="input @error('logo') is-invalid @enderror">
                @error('logo') <p class="field-error">{{ $message }}</p> @enderror

                <div wire:loading wire:target="logo" style="margin-top:8px;font-size:.82rem;color:var(--muted)">
                    Uploading...
                </div>

                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" style="margin-top:10px;height:60px;border:1px solid var(--line);border-radius:6px;padding:6px">
                @elseif ($logo_path)
                    <div style="margin-top:10px;display:flex;align-items:center;gap:10px">
                        <img src="{{ Storage::url($logo_path) }}" style="height:60px;border:1px solid var(--line);border-radius:6px;padding:6px">
                        <span style="font-size:.8rem;color:var(--muted)">Current logo (choose a file above to replace)</span>
                    </div>
                @endif
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
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">LOGO</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NAME</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NOTE</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">FRENCH</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">STATUS</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:10px 16px">
                        @if ($item->logo)
                            <img src="{{ Storage::url($item->logo) }}" style="height:36px;border:1px solid var(--line);border-radius:4px;padding:3px">
                        @else
                            <span style="font-size:.8rem;color:var(--muted)">No logo</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->name }}</td>
                    <td style="padding:14px 16px;color:var(--muted);font-size:.85rem">{{ $item->getTranslation('note', 'en', false) ?: '-' }}</td>
                    <td style="padding:14px 16px;font-size:.82rem">
                        @if ($item->hasTranslation('note', 'fr'))
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
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this partner?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:24px 16px;text-align:center;color:var(--muted)">No partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>