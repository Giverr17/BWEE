<?php

use App\Models\Event;
use App\Models\EventRegistration;
use Livewire\Volt\Component;

new class extends Component
{
    public $selectedEventId = null;

    public $search = '';

    public $newsletterFilter = '';

    public $expandedRows = [];

    public function mount()
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        if ($events->isNotEmpty()) {
            $this->selectedEventId = (int) (request()->query('event_id') ?: $events->first()->id);
        }
    }

    public function toggleRow($id)
    {
        if (in_array($id, $this->expandedRows, true)) {
            $this->expandedRows = array_values(array_diff($this->expandedRows, [$id]));
        } else {
            $this->expandedRows[] = $id;
        }
    }

    public function delete($id)
    {
        $reg = EventRegistration::findOrFail($id);
        $reg->delete();
        $this->dispatch('notify', 'Registration deleted.');
    }

    public function exportCsv()
    {
        if (! $this->selectedEventId) {
            return;
        }

        $event = Event::findOrFail($this->selectedEventId);

        $registrations = EventRegistration::where('event_id', $this->selectedEventId)
            ->when($this->search, function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('profession', 'like', $term)
                        ->orWhere('organization', 'like', $term);
                });
            })
            ->when($this->newsletterFilter === 'yes', function ($query) {
                $query->where(function ($q) {
                    $q->where('newsletter_opt_in', true)
                        ->orWhere('newsletter_consent', true);
                });
            })
            ->when($this->newsletterFilter === 'no', function ($query) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('newsletter_opt_in', false)->orWhereNull('newsletter_opt_in');
                    })->where(function ($sub) {
                        $sub->where('newsletter_consent', false)->orWhereNull('newsletter_consent');
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $slugPart = $event->slug ?: ('event-' . $event->id);
        $filename = "{$slugPart}-registrations.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Full Name',
                'Email',
                'Phone',
                'Country of Residence',
                'Country Code',
                'Nationality',
                'Nationality Code',
                'Profession / Area of Interest',
                'Organization',
                'Expectations',
                'Consent Timestamp',
                'Locale',
                'Newsletter Opt-In',
                'Newsletter Consented At',
                'Registered At',
            ]);

            foreach ($registrations as $reg) {
                fputcsv($handle, [
                    $reg->id,
                    $reg->full_name,
                    $reg->email,
                    $reg->phone,
                    $reg->country_of_residence_name_en,
                    $reg->country_of_residence,
                    $reg->nationality_name_en,
                    $reg->nationality,
                    $reg->profession,
                    $reg->organization ?: '',
                    $reg->expectations,
                    $reg->consent_at ? $reg->consent_at->toIso8601String() : '',
                    $reg->locale,
                    ($reg->newsletter_opt_in || $reg->newsletter_consent) ? 'Yes' : 'No',
                    $reg->consented_at ? $reg->consented_at->toIso8601String() : '',
                    $reg->created_at ? $reg->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }

    public function with(): array
    {
        $events = Event::withCount('registrations')->orderBy('event_date', 'desc')->get();

        $registrations = collect();
        $selectedEvent = null;

        if ($this->selectedEventId) {
            $selectedEvent = $events->firstWhere('id', (int) $this->selectedEventId);

            $registrations = EventRegistration::where('event_id', $this->selectedEventId)
                ->when($this->search, function ($query) {
                    $term = '%' . trim($this->search) . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('full_name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('profession', 'like', $term)
                            ->orWhere('organization', 'like', $term);
                    });
                })
                ->when($this->newsletterFilter === 'yes', function ($query) {
                    $query->where(function ($q) {
                        $q->where('newsletter_opt_in', true)
                            ->orWhere('newsletter_consent', true);
                    });
                })
                ->when($this->newsletterFilter === 'no', function ($query) {
                    $query->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('newsletter_opt_in', false)->orWhereNull('newsletter_opt_in');
                        })->where(function ($sub) {
                            $sub->where('newsletter_consent', false)->orWhereNull('newsletter_consent');
                        });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return [
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'registrations' => $registrations,
        ];
    }
};
?>

<div>
    {{-- Header Controls: Event Selector, Search, Filter, Export --}}
    <div class="card" style="margin-bottom:20px;padding:16px 20px">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px">
            {{-- Left: Event dropdown, Search & Newsletter filter --}}
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:14px;flex:1;min-width:280px">
                <div style="min-width:220px;flex:1">
                    <label style="display:block;font-size:.72rem;letter-spacing:.04em;color:var(--muted);margin-bottom:4px;text-transform:uppercase">Select Event</label>
                    <select wire:model.live="selectedEventId" class="input" style="width:100%">
                        @forelse($events as $ev)
                            <option value="{{ $ev->id }}">
                                {{ $ev->getTranslation('title', 'en', false) }} ({{ $ev->registrations_count }} registrations)
                            </option>
                        @empty
                            <option value="">No events available</option>
                        @endforelse
                    </select>
                </div>

                <div style="min-width:180px;flex:1">
                    <label style="display:block;font-size:.72rem;letter-spacing:.04em;color:var(--muted);margin-bottom:4px;text-transform:uppercase">Search Registrations</label>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search name, email, org..."
                           class="input"
                           style="width:100%">
                </div>

                <div style="min-width:160px">
                    <label style="display:block;font-size:.72rem;letter-spacing:.04em;color:var(--muted);margin-bottom:4px;text-transform:uppercase">Newsletter</label>
                    <select wire:model.live="newsletterFilter" class="input" style="width:100%">
                        <option value="">All Registrants</option>
                        <option value="yes">Newsletter (Yes)</option>
                        <option value="no">No Newsletter (No)</option>
                    </select>
                </div>
            </div>

            {{-- Right: Export CSV & Message action --}}
            <div style="display:flex;align-items:center;gap:12px;margin-top:auto">
                @if($selectedEvent && $registrations->isNotEmpty())
                    <button type="button"
                            wire:click="exportCsv"
                            class="btn-primary"
                            style="width:auto;margin-top:0;display:inline-flex;align-items:center;gap:6px;font-size:.85rem;padding:9px 16px">
                        <span>📥</span>
                        <span>Export CSV</span>
                    </button>
                @endif
                @if($selectedEvent)
                    <a href="{{ route('admin.newsletter', ['audience' => 'event', 'event_id' => $selectedEvent->id]) }}"
                       class="btn-ghost"
                       style="width:auto;margin-top:0;display:inline-flex;align-items:center;gap:6px;font-size:.85rem;padding:8px 14px;text-decoration:none">
                        <span>✉</span>
                        <span>Message Event</span>
                    </a>
                @endif
            </div>
        </div>

        @if($selectedEvent)
            <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--line);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;font-size:.84rem;color:var(--muted)">
                <div>
                    Event: <strong style="color:var(--ink)">{{ $selectedEvent->getTranslation('title', 'en', false) }}</strong>
                    &bull; Registration:
                    @if($selectedEvent->isRegistrationOpen())
                        <span style="color:#34d399;font-weight:600">Open</span>
                    @else
                        <span style="color:var(--muted);font-weight:600">Closed</span>
                    @endif
                </div>
                <div>
                    Showing <strong style="color:var(--ink)">{{ count($registrations) }}</strong> registration{{ count($registrations) === 1 ? '' : 's' }}
                </div>
            </div>
        @endif
    </div>

    {{-- Registrations Table --}}
    <div class="card" style="padding:0;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--paper);border-bottom:1px solid var(--line)">
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NAME</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">EMAIL</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">PHONE</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">RESIDENCE</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NATIONALITY</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">PROFESSION</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">ORGANIZATION</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">NEWSLETTER</th>
                    <th style="text-align:left;padding:12px 14px;font-size:.72rem;letter-spacing:.04em;color:var(--muted)">REGISTERED</th>
                    <th style="padding:12px 14px;text-align:right"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                    @php $isExpanded = in_array($reg->id, $expandedRows, true); @endphp
                    <tr style="border-bottom:1px solid var(--line);{{ $isExpanded ? 'background:rgba(255,255,255,0.02);' : '' }}">
                        <td style="padding:12px 14px;font-weight:600;font-size:.88rem">
                            {{ $reg->full_name }}
                        </td>
                        <td style="padding:12px 14px;font-size:.84rem;color:var(--ink-soft)">
                            <a href="mailto:{{ $reg->email }}" style="color:var(--clay);text-decoration:none">{{ $reg->email }}</a>
                        </td>
                        <td style="padding:12px 14px;font-size:.84rem;color:var(--ink-soft);white-space:nowrap">
                            {{ $reg->phone }}
                        </td>
                        <td style="padding:12px 14px;font-size:.84rem;color:var(--ink-soft)">
                            <span title="{{ $reg->country_of_residence }}">{{ $reg->country_of_residence_name }}</span>
                        </td>
                        <td style="padding:12px 14px;font-size:.84rem;color:var(--ink-soft)">
                            <span title="{{ $reg->nationality }}">{{ $reg->nationality_name }}</span>
                        </td>
                        <td style="padding:12px 14px;font-size:.84rem;color:var(--ink-soft)">
                            {{ $reg->profession }}
                        </td>
                        <td style="padding:12px 14px;font-size:.84rem;color:var(--muted)">
                            {{ $reg->organization ?: '-' }}
                        </td>
                        <td style="padding:12px 14px;font-size:.82rem">
                            @if($reg->newsletter_opt_in || $reg->newsletter_consent)
                                <span style="color:#10b981;font-weight:600">Yes</span>
                            @else
                                <span style="color:var(--muted)">No</span>
                            @endif
                        </td>
                        <td style="padding:12px 14px;font-size:.8rem;color:var(--muted);white-space:nowrap">
                            {{ $reg->created_at ? $reg->created_at->format('M j, Y') : '' }}
                        </td>
                        <td style="padding:12px 14px;text-align:right;white-space:nowrap">
                            <button type="button"
                                    wire:click="toggleRow({{ $reg->id }})"
                                    class="btn-ghost"
                                    style="padding:5px 10px;font-size:.78rem"
                                    title="View expectations text">
                                {{ $isExpanded ? 'Hide' : 'Details' }}
                            </button>
                            <button type="button"
                                    wire:click="delete({{ $reg->id }})"
                                    wire:confirm="Are you sure you want to delete this registration for {{ $reg->full_name }}?"
                                    class="btn-ghost"
                                    style="padding:5px 10px;font-size:.78rem;color:var(--danger);border-color:#F3C9C6">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @if($isExpanded)
                        <tr style="background:var(--paper-dim);border-bottom:1px solid var(--line)">
                            <td colspan="10" style="padding:14px 18px">
                                <div style="display:flex;flex-direction:column;gap:6px">
                                    <div style="font-size:.75rem;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);font-weight:700">
                                        Expectations &amp; Goals:
                                    </div>
                                    <div style="font-size:.9rem;color:var(--ink);line-height:1.55;white-space:pre-line;background:var(--paper);padding:12px 16px;border-radius:8px;border:1px solid var(--line)">
                                        {{ $reg->expectations }}
                                    </div>
                                    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:4px;font-size:.76rem;color:var(--muted)">
                                        <span>Locale: <strong>{{ strtoupper($reg->locale) }}</strong></span>
                                        <span>Consent recorded: <strong>{{ $reg->consent_at ? $reg->consent_at->format('M j, Y H:i T') : 'Yes' }}</strong></span>
                                        <span>Newsletter: <strong>{{ ($reg->newsletter_opt_in || $reg->newsletter_consent) ? 'Yes' : 'No' }}</strong></span>
                                        <span>Newsletter consented at: <strong>{{ $reg->consented_at ? $reg->consented_at->format('M j, Y H:i T') : '-' }}</strong></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="10" style="padding:32px 16px;text-align:center;color:var(--muted)">
                            @if($selectedEvent)
                                @if($search)
                                    No registrations matching "{{ $search }}" found for this event.
                                @else
                                    No registrations recorded yet for this event.
                                @endif
                            @else
                                Please select an event to view its registrations.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
