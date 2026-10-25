<?php

use App\Models\Message;
use Livewire\Volt\Component;

new class extends Component
{
    public $items;
    public $expandedId = null;

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = Message::orderBy('created_at', 'desc')->get();
    }

    public function toggle($id)
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;

        if ($this->expandedId === $id) {
            $message = Message::find($id);
            if ($message && ! $message->read_at) {
                $message->update(['read_at' => now()]);
                $this->loadItems();
            }
        }
    }

    public function delete($id)
    {
        Message::findOrFail($id)->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Message deleted.');
    }
};
?>

<div>
    <p style="color:var(--muted);font-size:.9rem;margin-bottom:20px">
        {{ count($items) }} message{{ count($items) === 1 ? '' : 's' }}
        @php $unread = $items->whereNull('read_at')->count(); @endphp
        @if ($unread > 0)
            <span style="color:var(--purple);font-weight:600">· {{ $unread }} unread</span>
        @endif
    </p>

    <div class="card" style="padding:0;overflow:hidden">
        @forelse ($items as $item)
            <div wire:key="msg-{{ $item->id }}" style="border-bottom:1px solid var(--line)">
                <button wire:click="toggle({{ $item->id }})"
                    style="width:100%;text-align:left;background:{{ $expandedId === $item->id ? 'var(--paper)' : 'transparent' }};border:none;cursor:pointer;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;gap:16px">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0">
                        @if (!$item->read_at)
                            <span style="width:8px;height:8px;border-radius:50%;background:var(--purple);flex-shrink:0"></span>
                        @else
                            <span style="width:8px;height:8px;flex-shrink:0"></span>
                        @endif
                        <div style="min-width:0">
                            <div style="font-weight:{{ $item->read_at ? '500' : '700' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.93rem">
                                {{ $item->name }}
                                <span style="color:var(--muted);font-weight:400"> &mdash; {{ $item->subject ?? 'No subject' }}</span>
                            </div>
                            <div style="font-size:.78rem;color:var(--muted);margin-top:2px">{{ $item->email }}</div>
                        </div>
                    </div>
                    <span style="font-size:.75rem;color:var(--muted)">{{ $item->created_at->format('M j, Y') }}</span>
                </button>

                @if ($expandedId === $item->id)
                    <div style="padding:0 18px 18px;background:var(--paper)">
                        <div style="display:flex;align-items:center;gap:10px;padding:10px 0 12px;border-bottom:1px solid var(--line);margin-bottom:14px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:var(--purple);color:#FBF7F1;font-size:.75rem;font-weight:700">
                                {{ strtoupper(substr($item->name, 0, 1)) }}
                            </span>
                            <div>
                                <div style="font-size:.88rem;font-weight:600">{{ $item->name }}</div>
                                <div style="font-size:.75rem;color:var(--muted)">{{ $item->email }} &middot; {{ $item->created_at->format('M j, Y') }}</div>
                            </div>
                        </div>

                        <p style="font-size:.92rem;line-height:1.7;white-space:pre-wrap">{{ $item->body }}</p>

                        <div style="display:flex;align-items:center;gap:8px;margin-top:16px;padding-top:14px;border-top:1px solid var(--line)">
                            <a href="mailto:{{ $item->email }}" class="btn-ghost" style="text-decoration:none">Reply by Email</a>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $item->email }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Email', 1500)" class="btn-ghost">Copy Email</button>
                            <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this message?" class="btn-ghost" style="color:var(--danger);border-color:#F3C9C6;margin-left:auto">Delete</button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div style="padding:48px 18px;text-align:center;color:var(--muted)">No messages yet.</div>
        @endforelse
    </div>
</div>