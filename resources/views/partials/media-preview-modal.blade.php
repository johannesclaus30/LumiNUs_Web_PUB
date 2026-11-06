{{-- 
    Media Preview Modal Partial
    Usage: @include('partials.media-preview-modal')
    
    This modal handles:
    - Image previews (with zoom, rotate, download)
    - Video previews
    - Document previews (with download option)
    - PDF previews (embedded viewer)
--}}

<!-- Media Preview Modal -->
<div class="media-preview-modal-overlay" id="mediaPreviewModal" onclick="closeMediaPreview(event)">
    <div class="media-preview-modal" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="media-preview-header">
            <div class="media-preview-info">
                <div class="media-preview-icon" id="mediaPreviewIcon">
                    <i class="fa-solid fa-image"></i>
                </div>
                <div class="media-preview-meta">
                    <h4 id="mediaPreviewTitle">Image Preview</h4>
                    <span id="mediaPreviewSubtitle">Loading...</span>
                </div>
            </div>
            <div class="media-preview-actions">
                <!-- Zoom controls (for images) -->
                <div class="media-preview-zoom-controls" id="mediaZoomControls" style="display: none;">
                    <button onclick="zoomOut()" title="Zoom Out">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                    </button>
                    <span id="zoomLevel">100%</span>
                    <button onclick="zoomIn()" title="Zoom In">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </button>
                    <button onclick="resetZoom()" title="Reset Zoom">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
                
                <!-- Rotate controls (for images) -->
                <div class="media-preview-rotate-controls" id="mediaRotateControls" style="display: none;">
                    <button onclick="rotateImage(-90)" title="Rotate Left">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button onclick="rotateImage(90)" title="Rotate Right">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
                
                <!-- Download button -->
                <button class="media-preview-download" id="mediaDownloadBtn" onclick="downloadMedia()" title="Download">
                    <i class="fa-solid fa-download"></i>
                </button>
                
                <!-- Close button -->
                <button class="media-preview-close" onclick="closeMediaPreview()" title="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="media-preview-content" id="mediaPreviewContent">
            <!-- Image Preview -->
            <div class="media-preview-image-container" id="mediaImageContainer" style="display: none;">
                <img id="mediaPreviewImage" src="" alt="Preview" onload="onMediaLoad()" onerror="onMediaError()">
                <div class="media-preview-loading" id="mediaImageLoading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Loading image...</span>
                </div>
            </div>
            
            <!-- Video Preview -->
            <div class="media-preview-video-container" id="mediaVideoContainer" style="display: none;">
                <video id="mediaPreviewVideo" controls onloadeddata="onMediaLoad()" onerror="onMediaError()">
                    Your browser does not support the video tag.
                </video>
                <div class="media-preview-loading" id="mediaVideoLoading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Loading video...</span>
                </div>
            </div>
            
            <!-- PDF Preview -->
            <div class="media-preview-pdf-container" id="mediaPdfContainer" style="display: none;">
                <iframe id="mediaPreviewPdf" src="" frameborder="0"></iframe>
                <div class="media-preview-loading" id="mediaPdfLoading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Loading PDF...</span>
                </div>
            </div>
            
            <!-- Document Preview (fallback) -->
            <div class="media-preview-document-container" id="mediaDocumentContainer" style="display: none;">
                <div class="media-preview-document-icon" id="mediaDocumentIcon">
                    <i class="fa-solid fa-file"></i>
                </div>
                <h3 id="mediaDocumentName">Document</h3>
                <p id="mediaDocumentSize">File size</p>
                <p class="media-preview-document-hint">
                    This file type cannot be previewed directly.
                </p>
                <button class="media-preview-download-btn" onclick="downloadMedia()">
                    <i class="fa-solid fa-download"></i>
                    Download File
                </button>
            </div>
            
            <!-- Error State -->
            <div class="media-preview-error" id="mediaErrorContainer" style="display: none;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>Failed to load</h3>
                <p>The file could not be previewed.</p>
                <button onclick="downloadMedia()">
                    <i class="fa-solid fa-download"></i>
                    Try Downloading Instead
                </button>
            </div>
        </div>
        
        <!-- Footer (for navigation between multiple files) -->
        <div class="media-preview-footer" id="mediaPreviewFooter" style="display: none;">
            <button class="media-nav-btn" id="mediaPrevBtn" onclick="navigateMedia(-1)">
                <i class="fa-solid fa-chevron-left"></i>
                Previous
            </button>
            <span class="media-nav-indicator" id="mediaNavIndicator">1 of 1</span>
            <button class="media-nav-btn" id="mediaNextBtn" onclick="navigateMedia(1)">
                Next
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay for Download -->
<div class="media-download-overlay" id="mediaDownloadOverlay" style="display: none;">
    <div class="media-download-spinner">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <span>Preparing download...</span>
    </div>
</div>