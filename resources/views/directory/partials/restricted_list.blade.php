{{-- resources/views/directory/partials/restricted_list.blade.php --}}
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
            
            $reasons = \App\Models\Alumni::getRestrictionReasons();
            $reasonLabel = $reasons[$alumnus->restriction_reason] ?? $alumnus->restriction_reason ?? 'No reason provided';
            $restrictedDate = optional($alumnus->restricted_at)->format('M d, Y h:i A') ?: 'Unknown';
            $restrictedDays = optional($alumnus->restricted_at)->diffInDays(now()) ?: 0;
            
            // Get admin who restricted this account
            $restrictedByName = optional($alumnus->restrictedBy)->admin_first_name 
                ? optional($alumnus->restrictedBy)->admin_first_name . ' ' . optional($alumnus->restrictedBy)->admin_last_name 
                : 'Unknown Admin';
        @endphp
        <!-- CRITICAL FIX: Added data-id attribute here -->
        <article class="alumni-card restricted-card" data-id="{{ $alumnus->id }}">
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
                
                <!-- Status Badge -->
                <div class="alumni-status-badge restricted">
                    <i class="fa-solid fa-user-slash"></i>
                    <span>Restricted</span>
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
                        <div class="meta-item restricted-date">
                            <i class="fa-regular fa-clock"></i>
                            <span>Restricted: {{ $restrictedDate }}</span>
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

                    <!-- Restriction Info - Enhanced -->
                    <div class="restriction-info">
                        <div class="restriction-header">
                            <span class="reason-label">
                                <i class="fa-solid fa-gavel"></i> Reason for Restriction
                            </span>
                            <span class="restricted-by">
                                <i class="fa-solid fa-user-shield"></i> {{ $restrictedByName }}
                            </span>
                        </div>
                        <div class="reason-text">
                            <span class="reason-badge">{{ $reasonLabel }}</span>
                        </div>
                        @if($alumnus->restriction_comment)
                        <div class="comment-text">
                            <i class="fa-solid fa-quote-left"></i>
                            {{ $alumnus->restriction_comment }}
                            <i class="fa-solid fa-quote-right"></i>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="alumni-card-footer restricted-footer">
                    <div class="alumni-status">
                        <span class="status-dot restricted"></span>
                        <span>Account Restricted</span>
                    </div>
                    
                    <div class="alumni-actions">
                        <button type="button" 
                                class="btn-action btn-unrestrict" 
                                onclick="prepareUnrestrict('{{ $alumnus->id }}', '{{ addslashes($displayName) }}')"
                                title="Unrestrict Account">
                            <i class="fa-solid fa-user-check"></i>
                            <span class="action-tooltip">Unrestrict</span>
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
        <div class="empty-icon restricted-empty">
            <i class="fa-solid fa-user-check"></i>
        </div>
    </div>
    <h3 class="empty-title">No Restricted Accounts</h3>
    <p class="empty-description">
        There are no restricted alumni accounts at this time.
        <br>Restricted accounts will appear here when you restrict them from the main directory.
    </p>
    <a href="/admin/directory" class="btn btn-primary">
        <i class="fa-solid fa-arrow-left"></i> Back to Directory
    </a>
</div>
@endif

{{-- This is restricted_list.blade.php --}}