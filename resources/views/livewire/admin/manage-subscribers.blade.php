<?php

use Livewire\Volt\Component;
use App\Models\NewsletterSubscriber;

new class extends Component
{
    public $items;

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = NewsletterSubscriber::orderBy('subscribed_at', 'desc')->get();
    }

    public function delete($id)
    {
        NewsletterSubscriber::findOrFail($id)->delete();
        $this->loadItems();
        $this->dispatch('notify', 'Subscriber removed.');
    }

    public function exportCsv()
    {
        $filename = 'bwee-subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Subscribed At']);
            foreach ($this->items as $item) {
                fputcsv($handle, [$item->name, $item->email, $item->subscribed_at]);
            }
            fclose($handle);
        }, $filename);
    }
};
?>

<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <p style="color:var(--muted);font-size:.9rem">{{ count($items) }} subscriber{{ count($items) === 1 ? '' : 's' }}</p>
        @if (count($items) > 0)
            <button wire:click="exportCsv" class="btn-ghost">Export CSV</button>
        @endif
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--paper);border-bottom:1px solid var(--line)">
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NAME</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">EMAIL</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">SUBSCRIBED</th>
                    <th style="padding:12px 16px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:14px 16px">{{ $item->name ?: '-' }}</td>
                    <td style="padding:14px 16px;font-weight:500">{{ $item->email }}</td>
                    <td style="padding:14px 16px;color:var(--muted);font-size:.85rem">{{ $item->subscribed_at?->format('M j, Y') }}</td>
                    <td style="padding:14px 16px;text-align:right">
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Remove this subscriber?" class="btn-ghost" style="padding:6px 12px;font-size:.8rem;color:var(--danger);border-color:#F3C9C6">Remove</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:24px 16px;text-align:center;color:var(--muted)">No subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>  