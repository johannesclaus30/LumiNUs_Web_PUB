@php
    /**
     * Announcement Upload Zone
     * Expects:
     *   $announcement   (optional — for edit mode)
     *   $maxImages      (default 5)
     *   $maxImageMb     (default 3)
     *   $maxVideoMb     (default 30)
     *   $existingImages (Collection — optional)
     *   $existingVideos (Collection — optional)
     */
    $maxImages      = $maxImages      ?? 5;
    $maxImageMb     = $maxImageMb     ?? 3;
    $maxVideoMb     = $maxVideoMb     ?? 30;

    $existingImages = $existingImages ?? collect();
    $existingVideos = $existingVideos ?? collect();

    $existingImageCount = $existingImages->count();
    $existingVideoCount = $existingVideos->count();
@endphp

<div class="upload-zone-card upload-zone-card-announcement"
     id="announcementUploadZone"
     data-max-images="{{ $maxImages }}"
     data-max-image-mb="{{ $maxImageMb }}"
     data-max-video-mb="{{ $maxVideoMb }}"
     data-existing-image-count="{{ $existingImageCount }}"
     data-existing-video-count="{{ $existingVideoCount }}">

    <div class="upload-zone-header">
        <div class="upload-zone-title">
            <i class="fa-solid fa-photo-film"></i>
            <span>Media</span>
        </div>
        <div class="upload-counter" id="uploadCounter">
            <span id="uploadCounterCurrent">{{ $existingImageCount }}</span>
            <span class="upload-counter-sep">/</span>
            <span>{{ $maxImages }}</span>
            <span class="upload-counter-label">images</span>
        </div>
    </div>

    {{-- Mutually exclusive notice --}}
    <div class="upload-zone-notice">
        <i class="fa-solid fa-circle-info"></i>
        <span>Upload <strong>either</strong> images <strong>or</strong> a single video — not both.</span>
    </div>

    {{-- ============ Images section ============ --}}
    <div class="upload-section" id="imageSection">
        <div class="upload-section-header">
            <i class="fa-regular fa-images"></i>
            <span>Images</span>
        </div>

        <div class="upload-dropzone upload-dropzone-images" id="uploadDropzone" tabindex="0" role="button">
            <div class="upload-dropzone-inner">
                <div class="upload-dropzone-icon">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="upload-dropzone-text">
                    <strong>Drop images here</strong>
                    <span>or <em>click to browse</em> · <em>paste from clipboard</em></span>
                </div>
                <div class="upload-dropzone-meta">
                    JPG · PNG · WEBP · max {{ $maxImageMb }}MB each · max {{ $maxImages }} total
                </div>
            </div>
        </div>

        <div class="upload-preview-grid" id="attachment-preview-container">
            @if($existingImages->isNotEmpty())
                @foreach($existingImages as $index => $image)
                    <div class="upload-tile existing-image {{ $index === 0 ? 'is-cover' : '' }}"
                         data-image-id="{{ $image->id }}"
                         data-type="existing"
                         draggable="true">
                        <img src="{{ $image->image_url }}"
                             alt="Announcement image {{ $index + 1 }}"
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
                                data-media-kind="image"
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

        <div class="upload-empty-hint" id="uploadEmptyHint" @if($existingImageCount > 0) style="display:none;" @endif>
            <i class="fa-regular fa-images"></i>
            <span>No images yet.</span>
        </div>

        <input type="file"
               name="images[]"
               id="attachmentInput"
               accept="image/jpeg,image/png,image/webp"
               multiple
               hidden>
    </div>

    {{-- ============ OR divider ============ --}}
    <div class="upload-or-divider" id="uploadOrDivider">
        <span class="upload-or-line"></span>
        <span class="upload-or-text">OR</span>
        <span class="upload-or-line"></span>
    </div>

    {{-- ============ Video section ============ --}}
    <div class="upload-section upload-section-video" id="videoSection">
        <div class="upload-section-header">
            <i class="fa-solid fa-video"></i>
            <span>Video</span>
            <span class="upload-section-hint">(single file)</span>
        </div>

        <div class="upload-dropzone upload-dropzone-video" id="videoDropzone" tabindex="0" role="button">
            <div class="upload-dropzone-inner">
                <div class="upload-dropzone-icon">
                    <i class="fa-solid fa-film"></i>
                </div>
                <div class="upload-dropzone-text">
                    <strong>Drop an MP4 here</strong>
                    <span>or <em>click to browse</em></span>
                </div>
                <div class="upload-dropzone-meta">
                    MP4 · max {{ $maxVideoMb }}MB
                </div>
            </div>
        </div>

        <div class="video-preview-slot" id="videoPreviewSlot">
            @if($existingVideos->isNotEmpty())
                @php $existingVideo = $existingVideos->first(); @endphp
                <div class="upload-tile video existing-video"
                     data-image-id="{{ $existingVideo->id }}"
                     data-type="existing-video">
                    <video src="{{ $existingVideo->image_url }}"
                           class="upload-tile-img"
                           preload="metadata"
                           muted></video>
                    <span class="upload-play-badge">
                        <i class="fa-solid fa-play"></i>
                    </span>

                    <button type="button"
                            class="upload-tile-remove"
                            data-action="mark-remove"
                            data-media-kind="video"
                            title="Remove video">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif
        </div>

        <input type="file"
               name="video"
               id="videoInput"
               accept="video/mp4"
               hidden>
    </div>

    {{-- ============ Hidden containers ============ --}}
    <div id="uploadRemovedIds"></div>
    <div id="deletedMediaContainer"></div>
</div>