/**
 * Media Preview Modal Module
 * Handles image, video, PDF, and document previews in a modal
 * instead of opening in new tabs
 */

// ============================================
// STATE
// ============================================
const MediaPreview = {
    isOpen: false,
    currentMedia: null,
    mediaList: [],
    currentIndex: 0,
    zoomLevel: 1,
    rotation: 0,
    maxZoom: 3,
    minZoom: 0.5,
    zoomStep: 0.25,
    rotationStep: 90
};

// ============================================
// OPEN MEDIA PREVIEW
// ============================================
/**
 * Open the media preview modal
 * @param {Object} media - The media object to preview
 * @param {string} media.url - The URL of the media
 * @param {string} media.type - The type: 'image', 'video', 'pdf', 'document'
 * @param {string} media.name - The filename
 * @param {number} media.size - The file size in bytes (optional)
 * @param {string} media.id - Optional ID for tracking
 * @param {Array} mediaList - Optional list of all media for navigation
 * @param {number} startIndex - Optional starting index
 */
function openMediaPreview(media, mediaList = null, startIndex = 0) {
    const modal = document.getElementById('mediaPreviewModal');
    if (!modal) {
        console.error('Media preview modal not found');
        return;
    }
    
    // Set up media list for navigation
    if (mediaList && mediaList.length > 0) {
        MediaPreview.mediaList = mediaList;
        MediaPreview.currentIndex = startIndex;
        MediaPreview.currentMedia = mediaList[startIndex];
    } else {
        MediaPreview.mediaList = [media];
        MediaPreview.currentIndex = 0;
        MediaPreview.currentMedia = media;
    }
    
    // Reset zoom and rotation
    MediaPreview.zoomLevel = 1;
    MediaPreview.rotation = 0;
    
    // Show modal
    modal.classList.add('active');
    MediaPreview.isOpen = true;
    document.body.style.overflow = 'hidden';
    
    // Load the media
    loadMediaPreview(MediaPreview.currentMedia);
    
    // Update navigation
    updateMediaNavigation();
    
    // Add keyboard listener
    document.addEventListener('keydown', handleMediaPreviewKeydown);
}

// ============================================
// LOAD MEDIA PREVIEW
// ============================================
function loadMediaPreview(media) {
    if (!media) return;
    
    // Get elements
    const iconEl = document.getElementById('mediaPreviewIcon');
    const titleEl = document.getElementById('mediaPreviewTitle');
    const subtitleEl = document.getElementById('mediaPreviewSubtitle');
    const imageContainer = document.getElementById('mediaImageContainer');
    const videoContainer = document.getElementById('mediaVideoContainer');
    const pdfContainer = document.getElementById('mediaPdfContainer');
    const documentContainer = document.getElementById('mediaDocumentContainer');
    const errorContainer = document.getElementById('mediaErrorContainer');
    const imageEl = document.getElementById('mediaPreviewImage');
    const videoEl = document.getElementById('mediaPreviewVideo');
    const pdfEl = document.getElementById('mediaPreviewPdf');
    const zoomControls = document.getElementById('mediaZoomControls');
    const rotateControls = document.getElementById('mediaRotateControls');
    
    // Hide all containers
    imageContainer.style.display = 'none';
    videoContainer.style.display = 'none';
    pdfContainer.style.display = 'none';
    documentContainer.style.display = 'none';
    errorContainer.style.display = 'none';
    zoomControls.style.display = 'none';
    rotateControls.style.display = 'none';
    
    // Update header info
    titleEl.textContent = media.name || 'File Preview';
    subtitleEl.textContent = media.size ? formatFileSize(media.size) : '';
    
    // Determine media type
    const mediaType = getMediaType(media);
    
    // Update icon
    iconEl.className = 'media-preview-icon ' + mediaType;
    iconEl.innerHTML = getMediaIcon(mediaType);
    
    // Show appropriate container
    switch (mediaType) {
        case 'image':
            imageContainer.style.display = 'flex';
            zoomControls.style.display = 'flex';
            rotateControls.style.display = 'flex';
            
            // Reset transforms
            imageEl.style.transform = `scale(1) rotate(0deg)`;
            document.getElementById('zoomLevel').textContent = '100%';
            
            // Show loading
            document.getElementById('mediaImageLoading').style.display = 'flex';
            
            // Set image source
            imageEl.src = media.url;
            break;
            
        case 'video':
            videoContainer.style.display = 'flex';
            
            // Show loading
            document.getElementById('mediaVideoLoading').style.display = 'flex';
            
            // Set video source
            videoEl.src = media.url;
            break;
            
        case 'pdf':
            pdfContainer.style.display = 'flex';
            
            // Show loading
            document.getElementById('mediaPdfLoading').style.display = 'flex';
            
            // Set PDF source
            pdfEl.src = media.url;
            
            // Hide loading after a delay (iframes don't have reliable load events)
            setTimeout(() => {
                document.getElementById('mediaPdfLoading').style.display = 'none';
            }, 1500);
            break;
            
        default:
            // Document - show download option
            documentContainer.style.display = 'flex';
            
            // Update document info
            document.getElementById('mediaDocumentIcon').innerHTML = getMediaIcon(mediaType);
            document.getElementById('mediaDocumentName').textContent = media.name || 'Document';
            document.getElementById('mediaDocumentSize').textContent = media.size ? formatFileSize(media.size) : 'Unknown size';
            break;
    }
    
    // Store current media
    MediaPreview.currentMedia = media;
}

// ============================================
// GET MEDIA TYPE
// ============================================
function getMediaType(media) {
    if (!media) return 'document';
    
    // Check explicit type first
    if (media.type) {
        const type = media.type.toLowerCase();
        if (type === 'image' || type === 'video' || type === 'pdf' || type === 'document') {
            return type;
        }
    }
    
    // Check by file extension
    const name = (media.name || '').toLowerCase();
    const extension = name.split('.').pop();
    
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'].includes(extension)) {
        return 'image';
    }
    if (['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'].includes(extension)) {
        return 'video';
    }
    if (extension === 'pdf') {
        return 'pdf';
    }
    
    return 'document';
}

// ============================================
// GET MEDIA ICON
// ============================================
function getMediaIcon(mediaType) {
    const icons = {
        image: '<i class="fa-solid fa-image"></i>',
        video: '<i class="fa-solid fa-video"></i>',
        pdf: '<i class="fa-solid fa-file-pdf"></i>',
        document: '<i class="fa-solid fa-file"></i>'
    };
    return icons[mediaType] || icons.document;
}

// ============================================
// ON MEDIA LOAD
// ============================================
function onMediaLoad() {
    // Hide loading spinners
    document.getElementById('mediaImageLoading').style.display = 'none';
    document.getElementById('mediaVideoLoading').style.display = 'none';
    document.getElementById('mediaPdfLoading').style.display = 'none';
}

// ============================================
// ON MEDIA ERROR
// ============================================
function onMediaError() {
    // Hide all containers
    document.getElementById('mediaImageContainer').style.display = 'none';
    document.getElementById('mediaVideoContainer').style.display = 'none';
    document.getElementById('mediaPdfContainer').style.display = 'none';
    document.getElementById('mediaDocumentContainer').style.display = 'none';
    
    // Show error container
    document.getElementById('mediaErrorContainer').style.display = 'flex';
}

// ============================================
// CLOSE MEDIA PREVIEW
// ============================================
function closeMediaPreview(event) {
    // If event exists and target is not the overlay, don't close
    if (event && event.target !== event.currentTarget) {
        return;
    }
    
    const modal = document.getElementById('mediaPreviewModal');
    if (!modal) return;
    
    modal.classList.remove('active');
    MediaPreview.isOpen = false;
    document.body.style.overflow = '';
    
    // Clear media sources to stop loading/playing
    const imageEl = document.getElementById('mediaPreviewImage');
    const videoEl = document.getElementById('mediaPreviewVideo');
    const pdfEl = document.getElementById('mediaPreviewPdf');
    
    if (imageEl) imageEl.src = '';
    if (videoEl) {
        videoEl.pause();
        videoEl.src = '';
    }
    if (pdfEl) pdfEl.src = '';
    
    // Remove keyboard listener
    document.removeEventListener('keydown', handleMediaPreviewKeydown);
    
    // Reset state
    MediaPreview.mediaList = [];
    MediaPreview.currentIndex = 0;
    MediaPreview.currentMedia = null;
    MediaPreview.zoomLevel = 1;
    MediaPreview.rotation = 0;
}

// ============================================
// ZOOM CONTROLS
// ============================================
function zoomIn() {
    if (MediaPreview.zoomLevel < MediaPreview.maxZoom) {
        MediaPreview.zoomLevel = Math.min(
            MediaPreview.zoomLevel + MediaPreview.zoomStep,
            MediaPreview.maxZoom
        );
        applyImageTransform();
    }
}

function zoomOut() {
    if (MediaPreview.zoomLevel > MediaPreview.minZoom) {
        MediaPreview.zoomLevel = Math.max(
            MediaPreview.zoomLevel - MediaPreview.zoomStep,
            MediaPreview.minZoom
        );
        applyImageTransform();
    }
}

function resetZoom() {
    MediaPreview.zoomLevel = 1;
    MediaPreview.rotation = 0;
    applyImageTransform();
}

function applyImageTransform() {
    const imageEl = document.getElementById('mediaPreviewImage');
    if (imageEl) {
        imageEl.style.transform = `scale(${MediaPreview.zoomLevel}) rotate(${MediaPreview.rotation}deg)`;
        document.getElementById('zoomLevel').textContent = Math.round(MediaPreview.zoomLevel * 100) + '%';
    }
}

// ============================================
// ROTATE CONTROLS
// ============================================
function rotateImage(degrees) {
    MediaPreview.rotation = (MediaPreview.rotation + degrees) % 360;
    applyImageTransform();
}

// ============================================
// DOWNLOAD MEDIA
// ============================================
async function downloadMedia() {
    const media = MediaPreview.currentMedia;
    if (!media || !media.url) {
        console.error('No media to download');
        return;
    }
    
    // Show download overlay
    const overlay = document.getElementById('mediaDownloadOverlay');
    if (overlay) overlay.style.display = 'flex';
    
    try {
        // Fetch the file as a blob
        const response = await fetch(media.url);
        
        if (!response.ok) {
            throw new Error('Failed to fetch file');
        }
        
        const blob = await response.blob();
        
        // Create a temporary URL for the blob
        const blobUrl = window.URL.createObjectURL(blob);
        
        // Create a temporary anchor element
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = media.name || 'download';
        
        // Append to body, click, and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up the blob URL
        setTimeout(() => {
            window.URL.revokeObjectURL(blobUrl);
        }, 1000);
        
    } catch (error) {
        console.error('Download failed:', error);
        
        // Fallback: open in new tab if fetch fails (might be CORS issue)
        // But we'll try to use the signed URL directly
        const link = document.createElement('a');
        link.href = media.url;
        link.download = media.name || 'download';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } finally {
        // Hide download overlay
        if (overlay) overlay.style.display = 'none';
    }
}

// ============================================
// NAVIGATION
// ============================================
function navigateMedia(direction) {
    const newIndex = MediaPreview.currentIndex + direction;
    
    if (newIndex < 0 || newIndex >= MediaPreview.mediaList.length) {
        return;
    }
    
    MediaPreview.currentIndex = newIndex;
    MediaPreview.zoomLevel = 1;
    MediaPreview.rotation = 0;
    
    loadMediaPreview(MediaPreview.mediaList[newIndex]);
    updateMediaNavigation();
}

function updateMediaNavigation() {
    const footer = document.getElementById('mediaPreviewFooter');
    const prevBtn = document.getElementById('mediaPrevBtn');
    const nextBtn = document.getElementById('mediaNextBtn');
    const indicator = document.getElementById('mediaNavIndicator');
    
    if (MediaPreview.mediaList.length <= 1) {
        footer.style.display = 'none';
        return;
    }
    
    footer.style.display = 'flex';
    indicator.textContent = `${MediaPreview.currentIndex + 1} of ${MediaPreview.mediaList.length}`;
    
    prevBtn.disabled = MediaPreview.currentIndex === 0;
    nextBtn.disabled = MediaPreview.currentIndex === MediaPreview.mediaList.length - 1;
}

// ============================================
// KEYBOARD HANDLER
// ============================================
function handleMediaPreviewKeydown(event) {
    if (!MediaPreview.isOpen) return;
    
    switch (event.key) {
        case 'Escape':
            closeMediaPreview();
            break;
        case 'ArrowLeft':
            if (MediaPreview.mediaList.length > 1) {
                navigateMedia(-1);
            }
            break;
        case 'ArrowRight':
            if (MediaPreview.mediaList.length > 1) {
                navigateMedia(1);
            }
            break;
        case '+':
        case '=':
            zoomIn();
            break;
        case '-':
            zoomOut();
            break;
        case '0':
            resetZoom();
            break;
    }
}

// ============================================
// HELPER: FORMAT FILE SIZE
// ============================================
function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    
    const units = ['B', 'KB', 'MB', 'GB'];
    const k = 1024;
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + units[i];
}

// ============================================
// INTEGRATION: UPDATE ATTACHMENT RENDERING
// ============================================
/**
 * This function replaces the old attachment rendering that opened in new tabs.
 * Call this from your existing renderAttachments function.
 */
function renderAttachmentWithPreview(att, isSent) {
    if (!att || !att.url) {
        return '';
    }
    
    const mediaType = getMediaType(att);
    const isImage = mediaType === 'image';
    const fileName = att.name || 'File';
    const fileSize = att.size ? formatFileSize(att.size) : '';
    
    // Escape for use in onclick (simple approach)
    const escapedName = fileName.replace(/'/g, "\\'").replace(/"/g, '\\"');
    const escapedUrl = att.url.replace(/'/g, "\\'");
    
    if (isImage) {
        return `
            <div class="message-attachment">
                <img 
                    src="${att.url}" 
                    alt="${escapeHtml(fileName)}" 
                    loading="lazy"
                    onclick="openMediaPreview({
                        url: '${escapedUrl}',
                        type: 'image',
                        name: '${escapedName}',
                        size: ${att.size || 0},
                        id: '${att.id || ''}'
                    })"
                    style="cursor: pointer;"
                    onerror="this.parentElement.innerHTML = '<div class=\\'file-error\\'><i class=\\'fa-solid fa-file-image\\'></i> Failed to load</div>'"
                >
            </div>
        `;
    }
    
    // For non-images, show a clickable file card
    const icon = getFileIconByType(mediaType, fileName);
    
    return `
        <div class="message-attachment">
            <div 
                class="file-download" 
                onclick="openMediaPreview({
                    url: '${escapedUrl}',
                    type: '${mediaType}',
                    name: '${escapedName}',
                    size: ${att.size || 0},
                    id: '${att.id || ''}'
                })"
                style="cursor: pointer;"
            >
                <i class="fa-solid ${icon}"></i>
                <div class="file-info">
                    <span class="file-name">${escapeHtml(fileName)}</span>
                    <span class="file-size">${fileSize}</span>
                </div>
                <i class="fa-solid fa-eye"></i>
            </div>
        </div>
    `;
}

// ============================================
// HELPER: GET FILE ICON BY TYPE
// ============================================
function getFileIconByType(mediaType, fileName) {
    if (mediaType === 'video') return 'fa-video';
    if (mediaType === 'pdf') return 'fa-file-pdf';
    
    // Check extension for more specific icons
    const ext = (fileName || '').toLowerCase().split('.').pop();
    
    const iconMap = {
        doc: 'fa-file-word',
        docx: 'fa-file-word',
        xls: 'fa-file-excel',
        xlsx: 'fa-file-excel',
        ppt: 'fa-file-powerpoint',
        pptx: 'fa-file-powerpoint',
        txt: 'fa-file-lines',
        csv: 'fa-file-csv',
        zip: 'fa-file-zipper',
        rar: 'fa-file-zipper'
    };
    
    return iconMap[ext] || 'fa-file';
}

// ============================================
// HELPER: ESCAPE HTML
// ============================================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// AUTO-INITIALIZE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Make sure modal exists
    if (!document.getElementById('mediaPreviewModal')) {
        console.warn('Media preview modal not found in DOM');
        return;
    }
    
    console.log('✅ Media Preview Modal initialized');
});