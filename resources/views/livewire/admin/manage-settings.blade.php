<?php

use App\Models\SiteSetting;
use App\Services\DeepLTranslator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $values = [];

    public $files = [];

    public $autoOn = true;

    public $touched = [];

    public $translateNote = '';

    public function mount()
    {
        $this->loadValues();
    }

    public function loadValues()
    {
        $settings = SiteSetting::all();

        foreach ($settings as $setting) {
            if (! in_array($setting->type, ['image', 'video', 'audio'])) {
                $this->values[$setting->id] = $setting->value;
            }
        }

        // Fresh session starts with untouched so typing English triggers auto-translation
        $this->touched = [];
        $this->translateNote = '';
    }

    public function save()
    {
        try {
            $allSettings = SiteSetting::all();

            foreach ($allSettings as $setting) {
                if (in_array($setting->type, ['image', 'video', 'audio'])) {
                    if (isset($this->files[$setting->id]) && $this->files[$setting->id]) {
                        if ($setting->value && ! str_starts_with($setting->value, 'http')) {
                            Storage::disk('public')->delete($setting->value);
                        }
                        $path = $this->files[$setting->id]->store('settings', 'public');
                        $setting->update(['value' => $path]);
                    }
                } else {
                    $setting->update(['value' => $this->values[$setting->id] ?? null]);
                }

                Cache::forget("site_setting:{$setting->key}");
            }

            $this->files = [];
            $this->loadValues();
            $this->dispatch('toast', type: 'success', message: 'Settings saved.');
        } catch (\Throwable $e) {
            Log::error('Failed to save site settings', ['error' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', message: 'Failed to save settings. Please try again.');
        }
    }

    protected function runTranslate(int $baseId): void
    {
        $base = SiteSetting::find($baseId);
        if (! $base) {
            return;
        }

        $twin = SiteSetting::where('key', $base->key.'_fr')->first();
        if (! $twin) {
            return;
        }

        $english = trim((string) ($this->values[$base->id] ?? ''));

        if (! $this->autoOn || ($this->touched[$twin->id] ?? false) || mb_strlen($english) < 2) {
            return;
        }

        $allowed = RateLimiter::attempt('deepl:'.auth()->id(), 150, fn () => true, 60);
        if (! $allowed) {
            $this->translateNote = 'Too many automatic translations. Wait a minute or type the French yourself.';

            return;
        }

        $result = app(DeepLTranslator::class)->translate(['setting' => $english]);

        if ($result === null || ! isset($result['setting'])) {
            $this->translateNote = 'Automatic translation is unavailable right now. You can type the French yourself.';

            return;
        }

        $this->values[$twin->id] = $result['setting'];
        $this->translateNote = '';
    }

    public function updated($name, $value)
    {
        if (preg_match('/^values\.(\d+)$/', $name, $matches)) {
            $id = (int) $matches[1];
            $setting = SiteSetting::find($id);
            if (! $setting) {
                return;
            }

            if (str_ends_with($setting->key, '_fr')) {
                $this->touched[$setting->id] = filled($value);

                return;
            }

            if (in_array($setting->type, ['image', 'video', 'audio', 'number'], true)) {
                return;
            }

            if (str_contains($setting->key, 'email') || str_contains($setting->key, 'url') || str_ends_with($setting->key, '_number')) {
                return;
            }

            $this->runTranslate($id);
        }
    }

    public function with()
    {
        $all = SiteSetting::orderBy('group')->orderBy('id')->get();
        $byKey = $all->keyBy('key');

        $groupLabels = [
            'general' => 'General Settings',
            'mission' => 'Mission & Affiliation',
            'about' => 'About & Vision',
            'stats' => 'Statistics',
            'launch' => 'Inaugural Launch Details',
            'involved' => 'Get Involved',
            'mail' => 'Email Throttling & Limits',
            'contact' => 'Contact Settings',
        ];

        $grouped = [];
        foreach ($all->groupBy('group') as $groupName => $settings) {
            $items = [];
            foreach ($settings as $setting) {
                if (str_ends_with($setting->key, '_fr')) {
                    continue;
                }

                $twin = $byKey->get($setting->key.'_fr');
                $items[] = [
                    'base' => $setting,
                    'twin' => $twin,
                ];
            }
            $label = $groupLabels[$groupName] ?? ucwords(str_replace('_', ' ', $groupName));
            $grouped[$label] = $items;
        }

        return [
            'grouped' => $grouped,
        ];
    }
};
?>

<div>
    <div style="margin-bottom:20px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
        <label class="check" style="margin-top:0">
            <input type="checkbox" wire:model="autoOn">
            Auto-translate English to French as I type (draft, review before saving)
        </label>
        <span wire:loading wire:target="values" style="color:var(--muted);font-size:.8rem">Translating...</span>
    </div>
    @if ($translateNote)
        <p class="field-error" style="margin-bottom:14px">{{ $translateNote }}</p>
    @endif
    <p style="color:var(--muted);font-size:.8rem;margin-bottom:20px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

    <form wire:submit="save">
        @foreach ($grouped as $groupLabel => $items)
            <div class="card" style="margin-bottom:20px">
                <h3 style="font-size:1.05rem;margin-bottom:16px">{{ $groupLabel }}</h3>

                @foreach ($items as $item)
                    @php
                        $base = $item['base'];
                        $twin = $item['twin'];
                        $isMultiLine = !in_array($base->type, ['number', 'image', 'video', 'audio']) &&
                                       !str_contains($base->key, 'url') &&
                                       !str_contains($base->key, 'email') &&
                                       !str_ends_with($base->key, '_number') &&
                                       !str_ends_with($base->key, '_cta') &&
                                       !str_ends_with($base->key, '_format') &&
                                       !str_ends_with($base->key, '_date') &&
                                       !str_ends_with($base->key, '_name') &&
                                       (str_contains($base->key, 'paragraph') || str_contains($base->key, 'text') || str_contains($base->key, 'intro') || strlen($base->value ?? '') > 90);
                    @endphp

                    @if ($twin)
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;{{ $loop->first ? '' : 'margin-top:16px' }}">
                            <div class="field" style="margin-top:0">
                                <label>{{ $base->label ?: $base->key }} (English)</label>
                                @if ($base->type === 'number')
                                    <input type="number" wire:model="values.{{ $base->id }}" class="input" style="max-width:200px">
                                @elseif ($isMultiLine)
                                    <textarea wire:model.live.debounce.300ms="values.{{ $base->id }}" rows="3" class="input" style="resize:vertical"></textarea>
                                @else
                                    <input type="text" wire:model.live.debounce.300ms="values.{{ $base->id }}" class="input">
                                @endif
                            </div>
                            <div class="field" style="margin-top:0">
                                <label>{{ $twin->label ?: ($base->label ?: $base->key) }} (Français)</label>
                                @if ($twin->type === 'number')
                                    <input type="number" wire:model="values.{{ $twin->id }}" class="input" style="max-width:200px">
                                @elseif ($isMultiLine)
                                    <textarea wire:model.live.blur="values.{{ $twin->id }}" rows="3" class="input" style="resize:vertical"></textarea>
                                @else
                                    <input type="text" wire:model.live.blur="values.{{ $twin->id }}" class="input">
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="field" style="{{ $loop->first ? 'margin-top:0' : 'margin-top:16px' }}">
                            <label>{{ $base->label ?: $base->key }}</label>

                            @if ($base->type === 'number' || str_ends_with($base->key, '_number') || $base->key === 'mail_daily_limit')
                                <input type="number" wire:model="values.{{ $base->id }}" class="input" style="max-width:220px">

                            @elseif (in_array($base->type, ['image', 'video', 'audio']))
                                <input type="file" wire:model="files.{{ $base->id }}"
                                       accept="{{ $base->type }}/*" class="input">
                                <div wire:loading wire:target="files.{{ $base->id }}" style="margin-top:8px;font-size:.82rem;color:var(--muted)">Uploading...</div>

                                @if (isset($files[$base->id]) && $files[$base->id])
                                    @if ($base->type === 'image')
                                        <img src="{{ $files[$base->id]->temporaryUrl() }}" style="margin-top:10px;height:70px;border-radius:8px;border:1px solid var(--line)">
                                    @else
                                        <p style="margin-top:8px;font-size:.82rem;color:var(--ok)">New file ready, will replace current on save.</p>
                                    @endif
                                @elseif ($base->value)
                                    <div style="margin-top:10px;display:flex;align-items:center;gap:10px">
                                        @if ($base->type === 'image')
                                            <img src="{{ str_starts_with($base->value, 'http') ? $base->value : Storage::url($base->value) }}" style="height:70px;border-radius:8px;border:1px solid var(--line)">
                                        @else
                                            <span style="font-size:.8rem;color:var(--muted)">Current: {{ basename($base->value) }}</span>
                                        @endif
                                    </div>
                                @else
                                    <p style="margin-top:8px;font-size:.78rem;color:var(--muted);font-style:italic">Not set yet.</p>
                                @endif

                            @elseif ($isMultiLine)
                                <textarea wire:model="values.{{ $base->id }}" rows="3" class="input" style="resize:vertical"></textarea>
                            @else
                                <input type="text" wire:model="values.{{ $base->id }}" class="input">
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach

        <button type="submit" class="btn-primary" style="width:auto;margin-top:0">Save All Settings</button>
    </form>
</div>
