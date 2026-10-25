<div class="event-card {{ $event->is_upcoming ? 'upcoming' : '' }}" @if(!empty($cardId)) id="{{ $cardId }}" @elseif(isset($loopIndex)) id="eventCard{{ $loopIndex }}" @endif>
  <div class="event-thumb" @if($event->thumbnail_url) style="background-image: url('{{ $event->thumbnail_url }}');" @endif>
    @if(!$event->thumbnail_url)
      <div class="stack-icon"><span></span><span></span><span>&#10022;</span></div>
      <div class="placeholder-caption">{{ $event->tag ?? ($event->is_upcoming ? 'Upcoming Summit' : 'Event') }}</div>
    @endif
  </div>
  <div class="event-body">
    <div class="event-tag">{{ $event->tag ?? ($event->is_upcoming ? 'Upcoming' : 'Event') }}</div>
    <div class="event-title">{{ $event->title }}</div>
    @if($event->date_label || $event->location)
      <div class="event-meta">
        {{ $event->date_label }}@if($event->date_label && $event->location) &bull; @endif{{ $event->location }}
      </div>
    @endif
    @if($event->description)
      <p style="color: var(--ink-soft); font-size: 0.88rem; margin-top: 10px; line-height: 1.5;">{{ $event->description }}</p>
    @endif
    @if($event->cta_text && $event->cta_url)
      <div style="margin-top: 16px;">
        <a href="{{ $event->cta_url }}" style="color: var(--clay); font-size: 0.88rem; font-weight: 600; text-decoration: none; border-bottom: 1.5px solid var(--marigold); padding-bottom: 2px;">
          {{ $event->cta_text }} &rarr;
        </a>
      </div>
    @endif
    @if($event->video_url)
      <div style="margin-top: 10px;">
        <a href="{{ $event->video_url }}" target="_blank" rel="noopener" style="color: var(--clay); font-size: 0.82rem; font-weight: 600;">&#9658; Watch Session</a>
      </div>
    @endif
  </div>
</div>
