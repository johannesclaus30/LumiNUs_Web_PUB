@php
    /**
     * Event Media Preview Component
     * Expects: $event (with ->images loaded)
     */
    
    $mediaItems = collect();
    
    if ($event->images && $event->images->count()) {
        foreach ($event->images as $item) {
            $path = $item->image_path ?? '';
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            
            $isVideo = in_array($ext, ['mp4', 'webm', 'mov', 'ogg'], true);
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true);
            
            if (!$isVideo && !$isImage) continue;
            
            $url = $item->image_url ?? null;
            if (!$url && method_exists($item, 'getUrl')) {
                $url = $item->getUrl();
            }
            
            $mediaItems->push([
                'id'        => $item->id,
                'type'      => $isVideo ? 'video' : 'image',
                'url'       => $url,
                'extension' => $ext,
            ]);
        }
    }
    
    $total     = $mediaItems->count();
    $visible   = $mediaItems->take(3);
    $remaining = max(0, $total - 3);
    $hasVideo  = $mediaItems->where('type', 'video')->count() > 0;
@endphp

<div class="event-media"
     data-media-count="{{ $total }}"
     data-media-has-video="{{ $hasVideo ? '1' : '0' }}"
     data-media-items="{{ $mediaItems->values()->toJson() }}">

    <div class="media-header">
        <span class="media-label">
            @if($hasVideo)
                <i class="fa-solid fa-photo-film"></i>
                <span>{{ $total }} {{ Str::plural('attachment', $total) }}</span>
                <span class="media-type-pill video-pill">
                    <i class="fa-solid fa-video"></i> Video
                </span>
            @elseif($total > 0)
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
                        class="media-tile {{ $media['type'] }}"
                        data-media-index="{{ $index }}"
                        data-media-type="{{ $media['type'] }}"
                        data-media-url="{{ $media['url'] }}"
                        aria-label="View {{ $media['type'] }} {{ $index + 1 }} of {{ $total }}">
                    
                    @if($media['type'] === 'image')
                        <img src="{{ $media['url'] }}"
                             alt="Event image {{ $index + 1 }}"
                             loading="lazy"
                             class="media-thumb">
                    @else
                        <video class="media-thumb media-video-thumb"
                               src="{{ $media['url'] }}#t=0.5"
                               preload="metadata"
                               muted
                               playsinline></video>
                        <span class="media-play-badge">
                            <i class="fa-solid fa-play"></i>
                        </span>
                    @endif

                    <span class="media-tile-overlay">
                        <i class="fa-solid {{ $media['type'] === 'video' ? 'fa-expand' : 'fa-magnifying-glass-plus' }}"></i>
                    </span>
                </button>
            @endforeach

            @if($remaining > 0)
                <button type="button"
                        class="media-tile more-tile"
                        data-media-index="3"
                        data-media-type="gallery"
                        aria-label="View all {{ $total }} attachments">
                    <span class="more-count">+{{ $remaining }}</span>
                    <span class="more-label">more</span>
                </button>
            @endif
        </div>
    @endif
</div>