<div class="gallery-card">
  <div class="gallery-media">
    @if($item->youtube_id)
      <div class="gallery-video-wrapper">
        <iframe
          src="{{ $item->embed_url }}"
          title="{{ $item->title }}"
          loading="lazy"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen>
        </iframe>
      </div>
    @elseif($item->photo_url)
      <img src="{{ $item->photo_url }}" alt="{{ $item->title }}" class="gallery-img">
    @else
      <div class="gallery-placeholder">
        <div class="stack-icon"><span></span><span></span><span>&#10022;</span></div>
        <div class="placeholder-caption">
          @if($item->type === 'video')
            {{ __('gallery.empty.video_pending') }}
          @else
            {{ __('gallery.empty.photo_pending') }}
          @endif
        </div>
      </div>
    @endif
  </div>
  <div class="gallery-body">
    <div class="gallery-type-badge">{{ $item->type === 'video' ? __('ui.labels.video') : __('ui.labels.photo_tag') }}</div>
    <h3 class="gallery-title">{{ $item->title }}</h3>
    @if($item->caption)
      <p class="gallery-caption">{{ $item->caption }}</p>
    @endif
    @if($item->recorded_at)
      <div class="gallery-date">{{ __('ui.labels.recorded_on') }} {{ $item->recorded_at->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'fr' ? 'd F Y' : 'F d, Y') }}</div>
    @endif
  </div>
</div>