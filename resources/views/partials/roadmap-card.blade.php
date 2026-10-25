@php
  $item = $roadmapItem ?? $item ?? $roadmap ?? null;
@endphp
@if($item)
<div class="program-card">
  <div class="program-card-head">
    <div class="program-icon-wrap" style="background: color-mix(in srgb, {{ $item->colour ?? 'var(--clay)' }} 12%, transparent); border-color: color-mix(in srgb, {{ $item->colour ?? 'var(--clay)' }} 30%, transparent);">
      <span class="program-icon">{{ $item->icon ?? '🚀' }}</span>
    </div>
    <span class="program-pillar-badge" style="color: {{ $item->colour ?? 'var(--clay)' }};">{{ __('ui.labels.roadmap') }} {{ $item->sort_order }}</span>
  </div>
  <h3 class="program-title">{{ $item->title }}</h3>
  <p class="program-desc">{{ $item->description }}</p>
</div>
@endif
