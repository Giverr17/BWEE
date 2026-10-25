<?php

use App\Models\Program;
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

    public $sort_order = 0;

    public $is_active = true;

    public function rules()
    {
        return [
            'slug' => ['required', 'alpha_dash', 'max:60'],
            'title_en' => ['required', 'min:3'],
            'title_fr' => ['nullable', 'min:3'],
            'description_en' => ['required', 'min:10'],
            'description_fr' => ['nullable', 'min:10'],
            'icon' => ['nullable', 'max:60'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = Program::orderBy('sort_order')->get();
    }

    public function edit($id)
    {
        $item = Program::findOrFail($id);
        $this->editingId = $id;
        $this->slug = $item->slug;
        $this->title_en = $item->getTranslation('title', 'en', false);
        $this->title_fr = $item->getTranslation('title', 'fr', false);
        $this->description_en = $item->getTranslation('description', 'en', false);
        $this->description_fr = $item->getTranslation('description', 'fr', false);
        $this->icon = $item->icon;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
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
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $item = Program::findOrFail($this->editingId);
            if (blank($this->title_fr)) {
                $item->forgetTranslation('title', 'fr');
            }
            if (blank($this->description_fr)) {
                $item->forgetTranslation('description', 'fr');
            }
            $item->update($data);
        } else {
            Program::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Program saved.');
    }

    public function delete($id)
    {
        Program::findOrFail($id)->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Program deleted.');
    }

    public function resetForm()
    {
        $this->reset(['slug', 'title_en', 'title_fr', 'description_en', 'description_fr', 'icon', 'sort_order', 'is_active', 'isEditing', 'editingId']);
        $this->is_active = true;
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} program{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : '$set(\'isEditing\', true)' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Program' }}
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
                        <label>Description (English)</label>
                        <textarea wire:model="description_en" rows="4" class="input @error('description_en') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('description_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Titre (Français)</label>
                        <input type="text" wire:model="title_fr" class="input @error('title_fr') is-invalid @enderror">
                        @error('title_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Description (Français)</label>
                        <textarea wire:model="description_fr" rows="4" class="input @error('description_fr') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('description_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site.</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:16px">
                <div class="field" style="margin-top:0">
                    <label>Slug</label>
                    <input type="text" wire:model="slug" placeholder="vocational-trades" class="input @error('slug') is-invalid @enderror">
                    @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field" style="margin-top:0">
                    <label>Icon (Material Symbol name, optional)</label>
                    <input type="text" wire:model="icon" placeholder="handshake" class="input @error('icon') is-invalid @enderror">
                    @error('icon') <p class="field-error">{{ $message }}</p> @enderror
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
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this program?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:24px 16px;text-align:center;color:var(--muted)">No programs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>