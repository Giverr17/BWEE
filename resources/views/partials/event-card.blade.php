<div class="event-card {{ $event->is_upcoming ? 'upcoming' : '' }}"
     x-data
     @if(!empty($cardId)) id="{{ $cardId }}" @elseif(isset($loopIndex)) id="eventCard{{ $loopIndex }}" @endif>
  <div class="event-thumb"
       style="cursor: pointer; @if($event->thumbnail_url) background-image: url('{{ $event->thumbnail_url }}'); @endif"
       @click="$dispatch('open-event-modal', {{ Js::from($event->modal_data) }})"
       onclick="window.openEventModal && window.openEventModal({{ Js::from($event->modal_data) }})"
       role="button"
       tabindex="0"
       aria-label="{{ $event->title }}">
    @if(!$event->thumbnail_url)
      <div class="stack-icon"><span></span><span></span><span>&#10022;</span></div>
      <div class="placeholder-caption">{{ $event->tag ?? ($event->is_upcoming ? 'Upcoming Summit' : 'Event') }}</div>
    @endif
  </div>

  <div class="event-body">
    <div class="event-tag">{{ $event->tag ?? ($event->is_upcoming ? 'Upcoming' : 'Event') }}</div>
    <div class="event-title"
         style="cursor: pointer;"
         @click="$dispatch('open-event-modal', {{ Js::from($event->modal_data) }})"
         onclick="window.openEventModal && window.openEventModal({{ Js::from($event->modal_data) }})"
         role="button"
         tabindex="0">
      {{ $event->title }}
    </div>

    @if($event->date_label || $event->location)
      <div class="event-meta">
        {{ $event->date_label }}@if($event->date_label && $event->location) &bull; @endif{{ $event->location }}
      </div>
    @endif

    @if($event->description)
      <p style="color: var(--ink-soft); font-size: 0.88rem; margin-top: 10px; line-height: 1.5;">
        {{ \Illuminate\Support\Str::limit($event->description, 220) }}
      </p>
    @endif

    {{-- Keynote Speakers Preview --}}
    @if(!empty($event->formatted_speakers))
      <div class="event-speakers-preview"
           style="margin-top: 16px; padding: 10px 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--line); border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 10px; cursor: pointer;"
           @click="$dispatch('open-event-modal', {{ Js::from($event->modal_data) }})"
           onclick="window.openEventModal && window.openEventModal({{ Js::from($event->modal_data) }})"
           title="{{ __('events.speakers_heading') }}">
        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
          {{-- Overlapping Avatars --}}
          <div style="display: flex; align-items: center; flex-shrink: 0;">
            @foreach(array_slice($event->formatted_speakers, 0, 4) as $speaker)
              @if(!empty($speaker['image_url']))
                <img src="{{ $speaker['image_url'] }}"
                     alt="{{ $speaker['name'] }}"
                     title="{{ $speaker['name'] }}"
                     style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--paper, #2B1660); margin-left: {{ $loop->first ? '0' : '-10px' }}; position: relative; z-index: {{ 10 - $loop->index }}; display: block;">
              @else
                <div title="{{ $speaker['name'] }}"
                     style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #E8C97A, #C9962C); color: #1C1B1F; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; justify-content: center; border: 2px solid var(--paper, #2B1660); margin-left: {{ $loop->first ? '0' : '-10px' }}; position: relative; z-index: {{ 10 - $loop->index }}; line-height: 1;">
                  {{ $speaker['initials'] ?? '?' }}
                </div>
              @endif
            @endforeach
          </div>

          {{-- Speaker Count & Top Names --}}
          <div style="min-width: 0;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--on-dark, #FBF7F1); line-height: 1.2;">
              {{ trans_choice(__('events.speakers_count'), count($event->formatted_speakers), ['count' => count($event->formatted_speakers)]) }}
            </div>
            <div style="font-size: 0.74rem; color: var(--clay, #14C3B5); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px; margin-top: 2px;">
              {{ implode(', ', array_slice(array_column($event->formatted_speakers, 'name'), 0, 2)) }}@if(count($event->formatted_speakers) > 2)...@endif
            </div>
          </div>
        </div>

        <span style="font-size: 0.76rem; color: var(--marigold-soft, #E8C97A); font-weight: 600; white-space: nowrap; flex-shrink: 0;">
          {{ __('events.learn_more') }} &rarr;
        </span>
      </div>
    @endif

    {{-- Actions / Learn More --}}
    <div style="margin-top: 16px; display: flex; flex-wrap: wrap; align-items: center; gap: 14px;">
      @if($event->isRegistrationOpen() && $event->slug)
        <a href="{{ route('events.register', $event) }}"
           class="btn btn-primary"
           style="padding: 6px 14px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
          <span>{{ __('events.register') }}</span> &rarr;
        </a>
      @endif

      <button type="button"
              @click="$dispatch('open-event-modal', {{ Js::from($event->modal_data) }})"
              onclick="window.openEventModal && window.openEventModal({{ Js::from($event->modal_data) }})"
              style="background: none; border: none; padding: 0; color: var(--clay); font-size: 0.88rem; font-weight: 600; cursor: pointer; border-bottom: 1.5px solid var(--marigold); padding-bottom: 2px; font-family: inherit;">
        {{ $event->cta_text ?: __('events.learn_more') }} &rarr;
      </button>

      @if($event->cta_url && $event->cta_url !== '#events')
        <a href="{{ $event->cta_url }}" target="_blank" rel="noopener" style="color: var(--clay); font-size: 0.88rem; font-weight: 600; text-decoration: none; border-bottom: 1.5px solid var(--marigold); padding-bottom: 2px;">
          {{ $event->cta_text }} &rarr;
        </a>
      @endif

      @if($event->video_url)
        <a href="{{ $event->video_url }}" target="_blank" rel="noopener" style="color: var(--clay); font-size: 0.82rem; font-weight: 600;">&#9658; {{ __('events.watch_session') }}</a>
      @endif
    </div>
  </div>
</div>
