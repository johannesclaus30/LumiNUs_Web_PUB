{{-- resources/views/directory/partials/archived_list.blade.php --}}
@if($alumni->count() > 0)
<div class="alumni-grid">
    @foreach($alumni as $alumnus)
        @php
            $firstName = $alumnus->first_name ?? '';
            $middleName = $alumnus->middle_name ?? '';
            $lastName = $alumnus->last_name ?? '';
            $email = $alumnus->email ?? '';
            $program = $alumnus->program ?? '';
            $graduationYear = optional($alumnus->year_graduated)->format('Y') ?: 'N/A';
            $middleInitial = $middleName !== '' ? strtoupper(mb_substr(trim($middleName), 0, 1)) . '.' : '';
            
            $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
            if ($photoPath === '') {
                $photoUrl = '/assets/FINAL-NULIPA.jpg';
            } elseif (preg_match('/^https?:\/\//i', $photoPath)) {
                $photoUrl = $photoPath;
            } elseif (str_starts_with($photoPath, '/storage/')) {
                $photoUrl = $photoPath;
            } elseif (str_starts_with($photoPath, 'storage/')) {
                $photoUrl = '/' . $photoPath;
            } elseif (str_starts_with($photoPath, '/')) {
                $photoUrl = $photoPath;
            } else {
                $photoUrl = asset('storage/' . ltrim($photoPath, '/'));
            }

            $displayName = trim($firstName . ' ' . ($middleInitial ? $middleInitial . ' ' : '') . $lastName);
            $initials = strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
            $archivedDate = optional($alumnus->deleted_at)->format('M d, Y h:i A') ?: 'Unknown';
            $archivedDays = optional($alumnus->deleted_at)->diffInDays(now()) ?: 0;
        @endphp
        <!-- CRITICAL: data-id attribute for JavaScript -->
        <article class="alumni-card archived-card" data-id="{{ $alumnus->id }}">
            <div class="alumni-card-wrapper">
                <div class="alumni-card-header">
                    <div class="alumni-photo-wrapper">
                        <img src="{{ $photoUrl }}" 
                             alt="{{ $displayName ?: 'Alumni photo' }}" 
                             class="alumni-photo"
                             onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                        @if(empty($photoPath))
                            <span class="photo-initials">{{ $initials }}</span>
                        @endif
                    </div>
                </div>
                
                <!-- Status Badge - Archived -->
                <div class="alumni-status-badge archived">
                    <i class="fa-solid fa-box-archive"></i>
                    <span>Archived</span>
                </div>
                
                <div class="alumni-card-body">
                    <div class="alumni-identity">
                        <h3 class="alumni-name">{{ $displayName ?: 'Unnamed Alumni' }}</h3>
                        <p class="alumni-program">
                            <i class="fa-solid fa-graduation-cap"></i>
                            {{ $program ?: 'Program not specified' }}
                        </p>
                    </div>
                    
                    <div class="alumni-meta">
                        <div class="meta-item">
                            <i class="fa-regular fa-calendar"></i>
                            <span>Graduated: {{ $graduationYear }}</span>
                        </div>
                        <div class="meta-item archived-date">
                            <i class="fa-regular fa-clock"></i>
                            <span>Archived: {{ $archivedDate }}</span>
                        </div>
                        @if($alumnus->student_id_number)
                        <div class="meta-item">
                            <i class="fa-solid fa-id-card"></i>
                            <span>ID: {{ $alumnus->student_id_number }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="alumni-contact">
                        <div class="contact-item">
                            <i class="fa-regular fa-envelope"></i>
                            <span class="contact-value">{{ $email ?: 'No email' }}</span>
                        </div>
                        @if($alumnus->phone_number)
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <span class="contact-value">{{ $alumnus->phone_number }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="alumni-card-footer archived-footer">
                    <div class="alumni-status">
                        <span class="status-dot archived"></span>
                        <span>Account Archived</span>
                    </div>
                    
                    <div class="alumni-actions">
                        <button type="button" 
                                class="btn-action btn-restore" 
                                onclick="prepareRestore('{{ $alumnus->id }}', '{{ addslashes($displayName) }}')"
                                title="Restore Account">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span class="action-tooltip">Restore</span>
                        </button>
                        <button type="button" 
                                class="btn-action btn-permanent-delete" 
                                onclick="preparePermanentDelete('{{ $alumnus->id }}', '{{ addslashes($displayName) }}')"
                                title="Permanently Delete">
                            <i class="fa-solid fa-trash-can"></i>
                            <span class="action-tooltip">Delete Permanently</span>
                        </button>
                    </div>
                </div>
            </div>
        </article>
    @endforeach
</div>

<!-- Pagination -->
@if (method_exists($alumni, 'links'))
<div class="pagination-wrapper">
    {{ $alumni->links() }}
</div>
@endif

@else
<div class="empty-state">
    <div class="empty-icon-wrapper">
        <div class="empty-icon archived-empty">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </div>
    <h3 class="empty-title">No Archived Accounts</h3>
    <p class="empty-description">
        There are no archived alumni accounts at this time.
        <br>Archived accounts will appear here when you archive them from the main directory.
    </p>
    <a href="/admin/directory" class="btn btn-primary">
        <i class="fa-solid fa-arrow-left"></i> Back to Directory
    </a>
</div>
@endif

{{-- This is archived_list.blade.php --}}