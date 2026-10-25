@if($partner->url)
  <a href="{{ $partner->url }}" target="_blank" rel="noopener" class="partner-mark" style="text-decoration: none;">
    @if($partner->logo_url)
      <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" style="height: 38px; width: auto; object-fit: contain;">
    @else
      {{ $partner->name }}
    @endif
  </a>
@else
  <div class="partner-mark">
    @if($partner->logo_url)
      <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" style="height: 38px; width: auto; object-fit: contain;">
    @else
      {{ $partner->name }}
    @endif
  </div>
@endif
