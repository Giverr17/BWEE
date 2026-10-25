<div class="program-card">
  <div class="program-card-head">
    <div class="program-icon-wrap" style="background: color-mix(in srgb, {{ $program->colour ?? 'var(--clay)' }} 12%, transparent); border-color: color-mix(in srgb, {{ $program->colour ?? 'var(--clay)' }} 30%, transparent);">
      <span class="program-icon">{{ $program->icon ?? '✨' }}</span>
    </div>
    <span class="program-pillar-badge" style="color: {{ $program->colour ?? 'var(--clay)' }};">{{ __('ui.labels.pillar') }} {{ $program->sort_order }}</span>
  </div>
  <h3 class="program-title">{{ $program->title }}</h3>
  <p class="program-desc">{{ $program->description }}</p>
</div>