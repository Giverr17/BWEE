<div class="founder-card">
  <div class="founder-photo">
    @if($founder->photo_url)
      <img src="{{ $founder->photo_url }}" alt="{{ $founder->name }}">
    @else
      <div class="stack-icon"><span></span><span></span><span>&#10022;</span></div>
      <div class="placeholder-caption">{{ __('ui.labels.photo') }}</div>
    @endif
  </div>
  <div>
    <div class="founder-name">{{ $founder->name }}</div>
    <div class="founder-role">{{ $founder->role }}</div>
    <p class="founder-bio">{{ $founder->bio }}</p>
    @if($founder->video_url)
      <div style="margin-top: 8px;">
        <a href="{{ $founder->video_url }}" target="_blank" rel="noopener" style="color: var(--clay); font-size: 0.85rem; font-weight: 600;">{{ __('ui.buttons.watch_video') }}</a>
      </div>
    @endif
    @if($founder->audio_url)
      <div style="margin-top: 8px;">
        <audio controls src="{{ $founder->audio_url }}" style="height: 32px; width: 100%; max-width: 260px;"></audio>
      </div>
    @endif
    @if($founder->linkedin_url || $founder->twitter_url || $founder->email)
      <div class="founder-socials" style="margin-top: 10px; display: flex; gap: 14px; font-size: 0.85rem; flex-wrap: wrap; word-break: break-word;">
        @if($founder->linkedin_url)
          <a href="{{ $founder->linkedin_url }}" target="_blank" rel="noopener" style="color: var(--clay); font-weight: 600; text-decoration: none;">LinkedIn &rarr;</a>
        @endif
        @if($founder->twitter_url)
          <a href="{{ $founder->twitter_url }}" target="_blank" rel="noopener" style="color: var(--clay); font-weight: 600; text-decoration: none;">Twitter / X &rarr;</a>
        @endif
        @if($founder->email)
          <a href="mailto:{{ $founder->email }}" style="color: var(--clay); font-weight: 600; font-size: 0.85rem; text-decoration: none; word-break: break-all;">{{ $founder->email }}</a>
        @endif
      </div>
    @endif
  </div>
</div>
