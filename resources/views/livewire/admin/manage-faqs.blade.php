<?php

use Livewire\Volt\Component;
use App\Models\FaqItem;
use Illuminate\Validation\Rule;
use App\Services\DeepLTranslator;
use Illuminate\Support\Facades\RateLimiter;

new class extends Component
{
    public $items;
    public $isEditing = false;
    public $editingId = null;

    public $question_en = '';
    public $question_fr = '';
    public $answer_en = '';
    public $answer_fr = '';

    // Live French translation
    public $autoOn = true;
    public $touched = [];      // fields where a person has typed French by hand
    public $translateNote = '';
    public $sort_order = 0;
    public $is_active = true;

    public function rules()
    {
        return [
            'question_en' => ['required', 'min:5'],
            'question_fr' => ['nullable', 'min:5'],
            'answer_en' => ['required', 'min:5'],
            'answer_fr' => ['nullable', 'min:5'],
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
        $this->items = FaqItem::orderBy('sort_order')->get();
    }

    public function edit($id)
    {
        $item = FaqItem::findOrFail($id);
        $this->editingId = $id;
        $this->question_en = $item->getTranslation('question', 'en', false);
        $this->question_fr = $item->getTranslation('question', 'fr', false);
        $this->answer_en = $item->getTranslation('answer', 'en', false);
        $this->answer_fr = $item->getTranslation('answer', 'fr', false);
        $this->sort_order = $item->sort_order;
        $this->is_active = $item->is_active;
        // Existing French counts as hand-written: never overwrite it automatically.
        $this->touched = [
            'question' => filled($this->question_fr),
            'answer' => filled($this->answer_fr),
        ];
        $this->translateNote = '';
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            // Empty French is left out so the public site falls back to English.
            'question' => array_filter(['en' => $this->question_en, 'fr' => $this->question_fr], 'filled'),
            'answer' => array_filter(['en' => $this->answer_en, 'fr' => $this->answer_fr], 'filled'),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            FaqItem::findOrFail($this->editingId)->update($data);
        } else {
            FaqItem::create($data);
        }

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('notify', 'FAQ item saved.');
    }

    public function runTranslate(string $field): void
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
                $this->translateNote = 'Auto translation unavailable. You can type the French yourself.';

                return;
            }

            $this->{$field.'_fr'} = $result[$field];
            $this->translateNote = '';
        } catch (\Throwable $e) {
            $this->translateNote = 'Auto translation unavailable. You can type the French yourself.';
        }
    }

    public function updatedQuestionEn() {}

    public function updatedAnswerEn() {}

    // Typing French by hand switches auto-translate off for that field; clearing it switches it back on.
    public function updatedQuestionFr($value) { $this->touched['question'] = filled($value); }

    public function updatedAnswerFr($value) { $this->touched['answer'] = filled($value); }

    public function delete($id)
    {
        FaqItem::findOrFail($id)->delete();
        $this->loadItems();
        $this->dispatch('notify', 'FAQ item deleted.');
    }

    public function resetForm()
    {
        $this->reset(['question_en', 'question_fr', 'answer_en', 'answer_fr', 'touched', 'translateNote', 'sort_order', 'is_active', 'isEditing', 'editingId']);
        $this->is_active = true;
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} item{{ count($items) === 1 ? '' : 's' }}</p>
        <button wire:click="{{ $isEditing ? 'resetForm' : '$set(\'isEditing\', true)' }}" class="btn-primary" style="width:auto;margin-top:0">
            {{ $isEditing ? 'Cancel' : 'Add FAQ Item' }}
        </button>
    </div>

    @if ($isEditing)
    <div class="card" style="margin-bottom:24px">
        <form wire:submit="save"
              x-data="adminForm({
                  formKey: 'faqs',
                  recordId: @entangle('editingId'),
                  pairs: [
                      { en: 'question_en', fr: 'question_fr', base: 'question' },
                      { en: 'answer_en', fr: 'answer_fr', base: 'answer' }
                  ]
              })">
            @include('admin.draft-restore-bar')

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Question (English)</label>
                        <input type="text" wire:model="question_en" name="question_en" class="input @error('question_en') is-invalid @enderror">
                        @error('question_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Answer (English)</label>
                        <textarea wire:model="answer_en" name="answer_en" rows="5" class="input @error('answer_en') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('answer_en') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <div class="field" style="margin-top:0">
                        <label>Question (Français)</label>
                        <input type="text" wire:model="question_fr" name="question_fr" class="input @error('question_fr') is-invalid @enderror">
                        @error('question_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>Réponse (Français)</label>
                        <textarea wire:model="answer_fr" name="answer_fr" rows="5" class="input @error('answer_fr') is-invalid @enderror" style="resize:vertical"></textarea>
                        @error('answer_fr') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                <label class="check" style="margin-top:0">
                    <input type="checkbox" x-model="autoOn" wire:model="autoOn">
                    Auto-translate English to French as I type (draft, review before saving)
                </label>
                <span x-show="translateStatus === 'translating'" x-cloak style="color:var(--muted);font-size:.8rem">Translating...</span>
            </div>
            <p x-show="translateNote" x-cloak x-text="translateNote" class="field-error" style="margin-top:8px"></p>
            @if ($translateNote)
                <p class="field-error" style="margin-top:8px">{{ $translateNote }}</p>
            @endif
            <p style="color:var(--muted);font-size:.8rem;margin-top:10px">Leave the French fields empty to show the English text on the French site. French you type yourself is never overwritten.</p>

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
                <button type="submit" class="btn-primary" style="width:auto;margin-top:0" wire:offline.attr="disabled">
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
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">QUESTION</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">FRENCH</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">STATUS</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:14px 16px;color:var(--muted)">{{ $item->sort_order }}</td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->getTranslation('question', 'en', false) }}</td>
                    <td style="padding:14px 16px;font-size:.82rem">
                        @if ($item->hasTranslation('question', 'fr') && $item->hasTranslation('answer', 'fr'))
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
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this FAQ item?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:24px 16px;text-align:center;color:var(--muted)">No FAQ items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
