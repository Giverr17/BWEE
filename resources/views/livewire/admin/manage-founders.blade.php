<?php

use App\Models\Founder;
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

    public $role_en = '';

    public $role_fr = '';

    public $email = '';

    public $bio_en = '';

    public $bio_fr = '';

    public $photo;

    public $photo_path;

    public $sort_order = 0;

    public $is_active = true;

    public $autoOn = true;

    public $touched = [];

    public $translateNote = '';

    protected array $translatable = ['role', 'bio'];

    public function rules()
    {
        return [
            'name' => ['required', 'min:2'],
            'role_en' => ['nullable', 'max:120'],
            'role_fr' => ['nullable', 'max:120'],
            'email' => ['nullable', 'email'],
            'bio_en' => ['nullable', 'min:10'],
            'bio_fr' => ['nullable', 'min:10'],
            'photo' => ['nullable', 'image', 'max:2048'],
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
        $this->items = Founder::orderBy('sort_order')->get();
    }

    public function openBlank()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $item = Founder::findOrFail($id);
        $this->editingId = $id;
        $this->name = $item->name;
        $this->role_en = $item->getTranslation('role', 'en', false);
        $this->role_fr = $item->getTranslation('role', 'fr', false);
        $this->email = $item->email;
        $this->bio_en = $item->getTranslation('bio', 'en', false);
        $this->bio_fr = $item->getTranslation('bio', 'fr', false);
        $this->photo_path = $item->photo;
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
        $this->touched = [
            'role' => filled($this->role_fr),
            'bio' => filled($this->bio_fr),
        ];
        $this->translateNote = '';
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'role' => array_filter(['en' => $this->role_en, 'fr' => $this->role_fr], 'filled'),
            'email' => $this->email ?: null,
            'bio' => array_filter(['en' => $this->bio_en, 'fr' => $this->bio_fr], 'filled'),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->photo) {
            $data['photo'] = $this->photo->store('founders', 'public');
        }

        if ($this->editingId) {
            $item = Founder::findOrFail($this->editingId);
            if ($this->photo && $item->photo) {
                Storage::disk('public')->delete($item->photo);
            }
            if (blank($this->role_fr)) {
                $item->forgetTranslation('role', 'fr');
            }
            if (blank($this->bio_fr)) {
                $item->forgetTranslation('bio', 'fr');
            }
            $item->update($data);
        } else {
            Founder::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'Founder saved.');
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
        $item = Founder::findOrFail($id);
        if ($item->photo) {
            Storage::disk('public')->delete($item->photo);
        }
        $item->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Founder deleted.');
    }

    public function resetForm()
    {
        $this->reset(['name', 'role_en', 'role_fr', 'email', 'bio_en', 'bio_fr', 'photo', 'photo_path', 'sort_order', 'is_active', 'isEditing', 'editingId', 'touched', 'translateNote']);
        $this->is_active = true;
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} founder{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : 'openBlank' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add Founder' }}
        </button>
    </div>

    @if ($isEditing)
    <div class="card" style="margin-bottom:24px">
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div class="field" style="margin-top:0">
                    <label>Full Name</label>
                    <input type="text" wire:model="name" class="input @error('name') is-invalid @enderror">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field" style="margin-top:0">
                    <label>Email</label>
                    <input type="email" wire:model="email" class="input @error('email') is-invalid @enderror">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:16px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Role / Title (English)</label>
                        <input type="text" wire:model.live.debounce.300ms="role_en" placeholder="Co-Founder" class="input @error('role_en') is-invalid @enderror">
                        @error('role_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Bio (English)</label>
                        <textarea wire:model.live.debounce.300ms="bio_en" rows="4" class="input @error('bio_en') is-invalid @enderror" style="resize:vertical" placeholder="Leave blank if not yet available"></textarea>
                        @error('bio_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Rôle / Titre (Français)</label>
                        <input type="text" wire:model.live.blur="role_fr" placeholder="Cofondatrice" class="input @error('role_fr') is-invalid @enderror">
                        @error('role_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Biographie (Français)</label>
                        <textarea wire:model.live.blur="bio_fr" rows="4" class="input @error('bio_fr') is-invalid @enderror" style="resize:vertical" placeholder="Laisser vide si pas encore disponible"></textarea>
                        @error('bio_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                <label class="check" style="margin-top:0">
                    <input type="checkbox" wire:model="autoOn">
                    Auto-translate English to French as I type (draft, review before saving)
                </label>
                <span wire:loading wire:target="role_en,bio_en" style="color:var(--muted);font-size:.8rem">Translating...</span>
            </div>
            @if ($translateNote)
                <p class="field-error" style="margin-top:8px">{{ $translateNote }}</p>
            @endif
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

            <div class="field" style="margin-top:18px">
                <label>Photo</label>
                <input type="file" wire:model="photo" accept="image/*" class="input @error('photo') is-invalid @enderror">
                @error('photo') <p class="field-error">{{ $message }}</p> @enderror

                <div wire:loading wire:target="photo" style="margin-top:8px;font-size:.82rem;color:var(--muted)">
                    Uploading...
                </div>

                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" style="margin-top:10px;height:90px;width:90px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                @elseif ($photo_path)
                    <div style="margin-top:10px;display:flex;align-items:center;gap:10px">
                        <img src="{{ Storage::url($photo_path) }}" style="height:90px;width:90px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                        <span style="font-size:.8rem;color:var(--muted)">Current photo (choose a file above to replace)</span>
                    </div>
                @else
                    <div style="margin-top:10px;height:90px;width:90px;border-radius:8px;border:1px dashed var(--line);display:flex;align-items:center;justify-content:center;font-size:.72rem;color:var(--muted);text-align:center;padding:6px">
                        No photo yet
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
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">PHOTO</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NAME</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">BIO</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">FRENCH</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">STATUS</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:10px 16px">
                        @if ($item->photo)
                            <img src="{{ Storage::url($item->photo) }}" style="height:40px;width:40px;object-fit:cover;border-radius:6px">
                        @else
                            <span style="font-size:.78rem;color:var(--muted);font-style:italic">Pending</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->name }}</td>
                    <td style="padding:14px 16px">
                        @if ($item->getTranslation('bio', 'en', false))
                            <span style="color:var(--ok);font-size:.82rem">Complete</span>
                        @else
                            <span style="color:var(--muted);font-size:.82rem;font-style:italic">Pending</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;font-size:.82rem">
                        @if ($item->hasTranslation('role', 'fr') && $item->hasTranslation('bio', 'fr'))
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
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this founder?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:24px 16px;text-align:center;color:var(--muted)">No founders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>