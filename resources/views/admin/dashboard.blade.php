<x-layouts.admin title="Dashboard">
    <div class="grid-4">
        <div class="card stat">
            <div class="n">{{ rescue(fn () => \App\Models\Message::whereNull('read_at')->count(), 0) }}</div>
            <div class="l">Unread messages</div>
        </div>
        <div class="card stat">
            <div class="n">{{ rescue(fn () => \App\Models\NewsletterSubscriber::count(), 0) }}</div>
            <div class="l">Newsletter subscribers</div>
        </div>
        <div class="card stat">
            <div class="n">{{ rescue(fn () => \App\Models\Event::count(), 0) }}</div>
            <div class="l">Events</div>
        </div>
        <div class="card stat">
            <div class="n">{{ rescue(fn () => \App\Models\Program::count(), 0) }}</div>
            <div class="l">Programs</div>
        </div>
    </div>

    <div class="card" style="margin-top:16px">
        <h3>Welcome, {{ auth()->user()->name }}</h3>
        <p style="margin-top:8px;color:var(--muted);line-height:1.6;font-size:.95rem">
            Content sections will appear in the sidebar as they are built.
        </p>
    </div>
</x-layouts.admin>