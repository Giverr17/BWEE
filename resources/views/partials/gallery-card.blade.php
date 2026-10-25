@php
    $photoCount = $item->photo_count;
    $photoUrls  = $item->photo_urls;
    $isAlbum    = $photoCount > 1;
    $locale     = app()->getLocale();
@endphp

@if ($isAlbum)
{{-- Album card with Alpine.js lightbox --}}
<div class="gallery-card"
     x-data="{
         open: false,
         index: 0,
         urls: {{ Js::from($photoUrls) }},
         get total() { return this.urls.length; },
         show(i) { this.index = i; this.open = true; document.body.style.overflow = 'hidden'; },
         close() { this.open = false; document.body.style.overflow = ''; },
         prev() { this.index = (this.index - 1 + this.total) % this.total; },
         next() { this.index = (this.index + 1) % this.total; },
     }"
     @keydown.window.escape="close()"
     @keydown.window.arrow-left="open && prev()"
     @keydown.window.arrow-right="open && next()">

    {{-- Cover image with badge --}}
    <div class="gallery-media" style="cursor:pointer;position:relative" @click="show(0)" role="button" tabindex="0"
         @keydown.enter="show(0)" aria-label="{{ $item->title }}">
        @if ($item->cover_url)
            <img src="{{ $item->cover_url }}" alt="{{ $item->title }}" class="gallery-img" loading="lazy">
        @else
            <div class="gallery-placeholder">
                <div class="stack-icon"><span></span><span></span><span>&#10022;</span></div>
            </div>
        @endif
        <span style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);color:#fff;font-size:.75rem;font-weight:700;padding:3px 8px;border-radius:99px;pointer-events:none">
            +{{ trans_choice(__('ui.labels.photos'), $photoCount, ['count' => $photoCount]) }}
        </span>
    </div>

    <div class="gallery-body">
        <div class="gallery-type-badge">{{ __('ui.labels.photo_tag') }}</div>
        <h3 class="gallery-title" style="cursor:pointer" @click="show(0)">{{ $item->title }}</h3>
        @if ($item->caption)
            <p class="gallery-caption">{{ $item->caption }}</p>
        @endif
        @if ($item->recorded_at)
            <div class="gallery-date">{{ __('ui.labels.recorded_on') }} {{ $item->recorded_at->locale($locale)->translatedFormat($locale === 'fr' ? 'd F Y' : 'F d, Y') }}</div>
        @endif
    </div>

    {{-- Lightbox overlay --}}
    <template x-teleport="body">
        <div x-cloak
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.92);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:16px"
             @click.self="close()"
             @touchstart.passive="$el._tx=$event.changedTouches[0].clientX"
             @touchend.passive="let dx=$event.changedTouches[0].clientX-$el._tx; if(Math.abs(dx)>50){ dx<0 ? next() : prev(); }"
             role="dialog" aria-modal="true" aria-label="{{ $item->title }}">

            {{-- Close --}}
            <button @click="close()"
                    aria-label="{{ __('ui.lightbox.close') }}"
                    style="position:absolute;top:14px;right:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);color:#fff;border-radius:8px;padding:7px 14px;font-size:.88rem;cursor:pointer;z-index:1">
                &#10005; {{ __('ui.lightbox.close') }}
            </button>

            {{-- Image --}}
            <img :src="urls[index]"
                 :alt="`{{ addslashes($item->title) }} - ` + (index + 1)"
                 loading="lazy"
                 style="max-width:100%;max-height:80vh;border-radius:8px;object-fit:contain">

            {{-- Navigation --}}
            <div style="display:flex;align-items:center;gap:20px;margin-top:16px">
                <button @click="prev()"
                        aria-label="{{ __('ui.lightbox.previous') }}"
                        style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);color:#fff;border-radius:8px;padding:8px 16px;font-size:1.1rem;cursor:pointer">
                    &#8592;
                </button>
                <span style="color:rgba(255,255,255,.8);font-size:.9rem">
                    <span x-text="index + 1"></span> {{ __('ui.lightbox.of') }} {{ $photoCount }}
                </span>
                <button @click="next()"
                        aria-label="{{ __('ui.lightbox.next') }}"
                        style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);color:#fff;border-radius:8px;padding:8px 16px;font-size:1.1rem;cursor:pointer">
                    &#8594;
                </button>
            </div>

            {{-- Caption --}}
            @if ($item->caption)
                <p style="color:rgba(255,255,255,.75);font-size:.88rem;margin-top:12px;max-width:64ch;text-align:center;line-height:1.5">{{ $item->caption }}</p>
            @endif
        </div>
    </template>
</div>

@else
{{-- Single photo or video card (unchanged behaviour) --}}
<div class="gallery-card">
    <div class="gallery-media">
        @if ($item->youtube_id)
            <div class="gallery-video-wrapper">
                <iframe
                    src="{{ $item->embed_url }}"
                    title="{{ $item->title }}"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
        @elseif ($item->cover_url)
            <img src="{{ $item->cover_url }}" alt="{{ $item->title }}" class="gallery-img" loading="lazy">
        @else
            <div class="gallery-placeholder">
                <div class="stack-icon"><span></span><span></span><span>&#10022;</span></div>
                <div class="placeholder-caption">
                    @if ($item->type === 'video')
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
        @if ($item->caption)
            <p class="gallery-caption">{{ $item->caption }}</p>
        @endif
        @if ($item->recorded_at)
            <div class="gallery-date">{{ __('ui.labels.recorded_on') }} {{ $item->recorded_at->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'fr' ? 'd F Y' : 'F d, Y') }}</div>
        @endif
    </div>
</div>
@endif