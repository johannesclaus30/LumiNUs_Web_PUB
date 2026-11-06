@php
    /**
     * Event Upload Zone
     * Expects:
     *   $event        (optional — for edit mode)
     *   $maxFiles     (default 5)
     *   $maxSizeMb    (default 5)
     *   $existing     (Collection of existing images — optional)
     */
    $maxFiles  = $maxFiles  ?? 5;
    $maxSizeMb = $maxSizeMb ?? 5;
    $existing  = $existing  ?? collect();
    $existingCount = $existing->count();
    $isEdit    = isset($event) && $event && $event->exists;
@endphp

<div class="upload-zone-card"
     id="eventUploadZone"
     data-max-files="{{ $maxFiles }}"
     data-max-size-mb="{{ $maxSizeMb }}"
     data-existing-count="{{ $existingCount }}">

    <div class="upload-zone-header">
        <div class="upload-zone-title">
            <i class="fa-solid fa-images"></i>
            <span>Event Images</span>
        </div>
        <div class="upload-counter" id="uploadCounter">
            <span id="uploadCounterCurrent">{{ $existingCount }}</span>
            <span class="upload-counter-sep">/</span>
            <span>{{ $maxFiles }}</span>
        </div>
    </div>

    {{-- Drop Area --}}
    <div class="upload-dropzone" id="uploadDropzone" tabindex="0" role="button">
        <div class="upload-dropzone-inner">
            <div class="upload-dropzone-icon">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <div class="upload-dropzone-text">
                <strong>Drop images here</strong>
                <span>or <em>click to browse</em> · <em>paste from clipboard</em></span>
            </div>
            <div class="upload-dropzone-meta">
                JPG · PNG · WEBP · max {{ $maxSizeMb }}MB each
            </div>
        </div>
    </div>

    {{-- Hidden native input --}}
    <input type="file"
           name="images[]"
           id="attachmentInput"
           accept="image/jpeg,image/png,image/webp"
           multiple
           hidden>

    {{-- Preview Grid --}}
    <div class="upload-preview-grid" id="attachment-preview-container">
        @if($existing->isNotEmpty())
            @foreach($existing as $index => $image)
                <div class="upload-tile existing-image {{ $index === 0 ? 'is-cover' : '' }}"
                     data-image-id="{{ $image->id }}"
                     data-type="existing"
                     draggable="true">
                    <img src="{{ $image->image_url }}"
                         alt="Event image {{ $index + 1 }}"
                         class="upload-tile-img"
                         onerror="this.onerror=null; this.src='{{ asset('assets/FINAL-NULIPA.jpg') }}';">

                    @if($index === 0)
                        <span class="upload-tile-cover-badge">
                            <i class="fa-solid fa-star"></i> Cover
                        </span>
                    @endif

                    <span class="upload-tile-index">{{ $index + 1 }}</span>

                    <button type="button"
                            class="upload-tile-remove"
                            data-action="mark-remove"
                            title="Remove image">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <span class="upload-tile-drag-handle" title="Drag to reorder">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Empty state --}}
    <div class="upload-empty-hint" id="uploadEmptyHint" @if($existingCount > 0) style="display:none;" @endif>
        <i class="fa-regular fa-images"></i>
        <span>No images yet. Add up to {{ $maxFiles }}.</span>
    </div>

    {{-- Container for dynamically-added remove_existing[] inputs --}}
    <div id="uploadRemovedIds"></div>
</div>