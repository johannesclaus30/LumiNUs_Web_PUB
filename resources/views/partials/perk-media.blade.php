@php
    /**
     * Perk Media Preview Component
     * Expects: $perk (with ->images loaded)
     */
    
    $mediaItems = collect();
    
    if ($perk->images && $perk->images->count()) {
        foreach ($perk->images as $item) {
            $path = $item->image_path ?? '';
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true);
            
            if (!$isImage) continue;
            
            $url = $item->image_url ?? null;
            if (!$url) continue;
            
            $mediaItems->push([
                'id'        => $item->id,
                'type'      => 'image',
                'url'       => $url,
                'extension' => $ext,
            ]);
        }
    }
    
    $total     = $mediaItems->count();
    $visible   = $mediaItems->take(3);
    $remaining = max(0, $total - 3);
    $fallback  = asset('assets/FINAL-NULIPA.jpg');
@endphp

<div class="perk-media"
     data-media-count="{{ $total }}"
     data-media-items="{{ $mediaItems->values()->toJson() }}">

    <div class="media-header">
        <span class="media-label">
            @if($total > 0)
                <i class="fa-regular fa-images"></i>
                <span>{{ $total }} {{ Str::plural('photo', $total) }}</span>
            @else
                <i class="fa-regular fa-image"></i>
                <span>No photos</span>
            @endif
        </span>
    </div>

    @if($total > 0)
        <div class="media-grid">
            @foreach($visible as $index => $media)
                <button type="button"
                        class="media-tile image"
                        data-media-index="{{ $index }}"
                        data-media-type="image"
                        data-media-url="{{ $media['url'] }}"
                        aria-label="View photo {{ $index + 1 }} of {{ $total }}">
                    <img src="{{ $media['url'] }}"
                         alt="Perk image {{ $index + 1 }}"
                         loading="lazy"
                         class="media-thumb"
                         onerror="this.onerror=null; this.src='{{ $fallback }}';">

                    <span class="media-tile-overlay">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </span>
                </button>
            @endforeach

            @if($remaining > 0)
                <button type="button"
                        class="media-tile more-tile"
                        data-media-index="3"
                        data-media-type="gallery"
                        aria-label="View all {{ $total }} photos">
                    <span class="more-count">+{{ $remaining }}</span>
                    <span class="more-label">more</span>
                </button>
            @endif
        </div>
    @else
        {{-- Fallback: single NULIPA placeholder when no images at all --}}
        <div class="media-grid media-grid-empty">
            <div class="media-tile image is-empty">
                <img src="{{ $fallback }}"
                     alt="No image"
                     class="media-thumb">
            </div>
        </div>
    @endif
</div>