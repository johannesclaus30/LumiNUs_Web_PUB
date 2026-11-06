<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/directory.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>

                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-current">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="directory-container admin-scrollable">
            @if (session('status'))
                <div class="settings-alert settings-alert-success" style="margin-top: 15px;">
                    <strong>{{ session('status') }}</strong>
                </div>
            @endif

            @if ($errors->any())
                <div class="settings-alert settings-alert-error" style="margin-top: 15px;">
                    <strong>Please review the form.</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            
            <div class="search-container">
                <input id="searchInput" type="text" placeholder="Search alumni by name or email..." class="search-bar" oninput="filterUsers()">
                <button id="createBtn" class="create-button" onclick="showModal()">Create Account</button>
            </div>
            <div class="user-container">
                @forelse ($alumni as $alumnus)
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
                        } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                            $photoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/');
                        } else {
                            $photoUrl = asset('storage/' . ltrim($photoPath, '/'));
                        }

                        $displayName = trim($firstName . ' ' . ($middleInitial ? $middleInitial . ' ' : '') . $lastName);
                    @endphp
                    <div class="user-box">
                        <img src="{{ $photoUrl }}" alt="{{ $displayName ?: 'Alumni photo' }}" class="user-photo">
                        <div class="primary-info">
                            <p class="field-label">User Information and Details</p>
                            <h1 class="name">{{ $displayName ?: 'Unnamed Alumni' }}</h1>
                            <p class="program">{{ $program ?: 'Program not specified' }}</p>
                        </div>
                        <div class="graduation-info">
                            <p class="field-label">Year<br>Graduated</p>
                            <p class="graduation-year">{{ $graduationYear }}</p>
                        </div>
                        <div class="contact-info">
                            <p class="field-label">Email</p>
                            <p class="email">{{ $email ?: 'Email not provided' }}</p>
                        </div>
                        
                        <div class="tools-container">
                            {{-- Message: Reserved --}}
                            <button type="button" class="tools-button" title="Coming Soon" 
                                    style="cursor: not-allowed; opacity: 0.5;" 
                                    onclick="alert('Message feature reserved for future updates.')">
                                <i class="fa-solid fa-comment-dots"></i>
                            </button>
                            
                            {{-- View & Edit: Active --}}
                            <a href="{{ route('admin.alumni.edit', $alumnus->id) }}" class="tools-button" title="View & Edit Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            
                            {{-- Disable: Reserved --}}
                            <button type="button" class="tools-button" title="Coming Soon" 
                                    style="cursor: not-allowed; opacity: 0.5;" 
                                    onclick="alert('Disable feature reserved for future updates.')">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>
                        </div>
                        
                    </div>
                @empty
                    <div class="user-box">
                        <div class="primary-info">
                            <h1 class="name">No alumni records found</h1>
                            <p class="program">Create an alumni account to show it here.</p>
                        </div>
                    </div>
                @endforelse

            </div>
        </div>
    </div>

<div id="createModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 style="color: #32418c; font-weight: 700;">Create Alumni Account</h2>
                <p class="muted" style="margin:0">Add an alumni record. The initial password will be password123 for testing.</p>
            </div>
            <button class="close-button" onclick="hideModal()">&times;</button>
        </div>

        <div class="modal-tabbar" role="tablist" aria-label="Alumni creation options">
            <button type="button" class="modal-tab active" data-modal-tab="single" onclick="switchModalMode('single')">Individual Creation</button>
            <button type="button" class="modal-tab" data-modal-tab="bulk" onclick="switchModalMode('bulk')">Bulk Import</button>
        </div>

        <div class="modal-body">
            <section class="modal-section modal-section-active" data-modal-section="single">
                <form id="singleCreateForm" method="POST" action="{{ route('admin.alumni.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-row">
                        <div>
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="Jane">
                        </div>
                        <div>
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="Dela">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Cruz">
                        </div>
                        <div>
                            <label for="student_id_number">Student ID Number</label>
                            <input id="student_id_number" name="student_id_number" value="{{ old('student_id_number') }}" required placeholder="2020-00001">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="date_of_birth">Date of Birth</label>
                            <input id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" type="date">
                        </div>
                        <div>
                            <label for="year_graduated">Year Graduated</label>
                            <input id="year_graduated" name="year_graduated" value="{{ old('year_graduated') }}" type="date">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="sex">Sex</label>
                            <select id="sex" name="sex">
                                <option value="">Select sex</option>
                                <option value="Male" @selected(old('sex') === 'Male')>Male</option>
                                <option value="Female" @selected(old('sex') === 'Female')>Female</option>
                                <option value="Prefer not to say" @selected(old('sex') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                        <div>
                            <label for="program">Program</label>
                            <input id="program" name="program" value="{{ old('program') }}" required placeholder="BS Computer Science">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="email">Email</label>
                            <input id="email" name="email" value="{{ old('email') }}" type="email" required placeholder="email@example.com">
                        </div>
                        <div>
                            <label for="phone_number">Phone Number</label>
                            <input id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="09xx xxx xxxx">
                        </div>
                    </div>

                    <div class="upload-card-panel">
                        <label for="card_photo">Card Photo</label>
                        <div class="upload-card-row">
                            <input id="card_photo" name="card_photo" type="file" accept="image/*">
                        </div>
                        <p class="upload-card-hint">Upload the alumni card photo here. This will be stored in the luminus_assets/card_photo folder.</p>
                    </div>

                    <div class="settings-form-note" style="margin-top: 18px;">
                        <strong>Testing password</strong>
                        <p style="margin: 6px 0 0;">All newly created alumni accounts will use <span style="font-weight: 700;">password123</span> for now.</p>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                        <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                    </div>
                </form>
            </section>

            <section class="modal-section" data-modal-section="bulk">
                <div class="bulk-import bulk-import-panel">
                    <div class="bulk-import-header">
                        <div>
                            <h3>Bulk Import</h3>
                            <p>Upload a CSV or Excel file to create multiple alumni accounts at once.</p>
                        </div>
                        <span class="settings-badge settings-badge-muted">CSV / XLSX</span>
                    </div>

                    <div class="bulk-import-body">
                        <input id="bulkImportFile" type="file" accept=".csv,.xls,.xlsx" />
                        <button type="button" class="btn btn-primary" onclick="handleBulkImport()">Import File</button>
                    </div>

                    <p id="bulkImportStatus" class="bulk-import-status">No file selected yet.</p>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
    // Basic search filter by name or email
    function filterUsers() {
        const q = document.getElementById('searchInput').value.toLowerCase().trim();
        document.querySelectorAll('.user-box').forEach(box => {
            const name = (box.querySelector('.name')?.textContent || '').toLowerCase();
            const email = (box.querySelector('.email')?.textContent || '').toLowerCase();
            const visible = !q || name.includes(q) || email.includes(q);
            box.style.display = visible ? 'flex' : 'none';
        });
    }

    function showModal() {
        document.getElementById('createModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function hideModal() {
        document.getElementById('createModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function switchModalMode(mode) {
        document.querySelectorAll('[data-modal-tab]').forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.modalTab === mode);
        });

        document.querySelectorAll('[data-modal-section]').forEach((section) => {
            section.classList.toggle('modal-section-active', section.dataset.modalSection === mode);
        });

        if (mode === 'bulk') {
            const status = document.getElementById('bulkImportStatus');
            if (status && status.textContent === 'No file selected yet.') {
                status.textContent = 'No file selected yet.';
            }
        }
    }

    // Maps the specific AAO Excel columns to our database fields
    function normalizeBulkRow(row) {
        // Helper to find case-insensitive key
        const findKey = (obj, possibleKeys) => {
            for (const key of possibleKeys) {
                if (obj[key] !== undefined) return obj[key];
                // Try case-insensitive match
                const found = Object.keys(obj).find(k => k.toLowerCase() === key.toLowerCase());
                if (found) return obj[found];
            }
            return '';
        };

        return {
            first_name: findKey(row, ['First Name', 'first_name', 'FirstName']).trim(),
            middle_name: findKey(row, ['Middle Name', 'middle_name', 'MiddleName']).trim(),
            last_name: findKey(row, ['Last Name', 'last_name', 'LastName']).trim(),
            student_id_number: findKey(row, ['Student ID', 'student_id_number', 'StudentID', 'Student ID Number']).trim(),
            program: findKey(row, ['Strand', 'program', 'Program', 'Department']).trim(),
            email: findKey(row, ['Personal Email', 'Official Email', 'email', 'Email', 'E-mail']).trim(),
            phone_number: findKey(row, ['Mobile No', 'phone_number', 'MobileNo', 'Mobile Number', 'Phone']).trim(),
            year_graduated: findKey(row, ['Graduation Period', 'year_graduated', 'GraduationPeriod', 'Year Graduated']).trim(),
            date_of_birth: '',
            sex: findKey(row, ['Sex', 'sex', 'Gender']).trim(),
        };
    }

    async function createAlumniRecord(record) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        
        Object.keys(record).forEach(key => {
            formData.append(key, record[key]);
        });

        const response = await fetch('{{ route('admin.alumni.store') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            const payload = await response.json().catch(() => null);
            const message = payload?.message || 'Unable to import one or more alumni records.';
            throw new Error(message);
        }
    }

        async function handleBulkImport() {
        const fileInput = document.getElementById('bulkImportFile');
        const status = document.getElementById('bulkImportStatus');
        const file = fileInput.files[0];

        if (!file) {
            status.textContent = 'Please choose a CSV or Excel file first.';
            return;
        }

        status.textContent = 'Reading file...';

        if (!window.XLSX) {
            status.textContent = 'Excel parsing library is unavailable right now.';
            return;
        }

        try {
            const buffer = await file.arrayBuffer();
            const workbook = XLSX.read(buffer, { type: 'array', cellDates: true });
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            
            // Convert to array of arrays to find header row
            const rawData = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false });
            
            // DEBUG: Log what we're seeing
            console.log('Raw data rows:', rawData.length);
            console.log('First 5 rows:', rawData.slice(0, 5));
            
            let headerRowIndex = -1;

            for (let i = 0; i < rawData.length; i++) {
                const row = rawData[i] || [];
                const rowString = row.join(' ').toLowerCase();
                
                // Check if this row contains our expected headers
                if (rowString.includes('student id') && rowString.includes('last name')) {
                    headerRowIndex = i;
                    console.log('✓ Found header row at index:', i);
                    console.log('Headers:', row);
                    break;
                }
            }

            if (headerRowIndex === -1) {
                status.textContent = 'Error: Could not find headers. Make sure the file has "Student ID" and "Last Name" columns.';
                console.error('Available rows:', rawData.map((r, i) => `Row ${i}: ${r.join(', ')}`).slice(0, 10));
                return;
            }

            // Parse JSON starting from header row
            const rows = XLSX.utils.sheet_to_json(sheet, { 
                range: headerRowIndex, 
                defval: '', 
                raw: false,
                dateNF: 'yyyy-mm-dd'
            });

            console.log('Parsed rows:', rows.length);
            if (rows.length > 0) {
                console.log('First row keys:', Object.keys(rows[0]));
                console.log('First row data:', rows[0]);
            }

            const records = rows
                .map(normalizeBulkRow)
                .filter((record) => {
                    const isValid = record.first_name && record.last_name && record.student_id_number;
                    if (!isValid) {
                        console.log('Skipping invalid record:', record);
                    }
                    return isValid;
                });

            if (!records.length) {
                status.textContent = 'No valid alumni records found. Check console for details.';
                return;
            }

            let created = 0;
            let failed = 0;

            status.textContent = `Importing ${records.length} alumni record(s). Please wait...`;

            for (const record of records) {
                try {
                    await createAlumniRecord(record);
                    created += 1;
                    status.textContent = `Progress: ${created}/${records.length} created...`;
                } catch (error) {
                    failed += 1;
                    console.error("Failed to import:", record.student_id_number, error.message);
                }
            }

            status.textContent = `✓ Import complete: ${created} created${failed ? `, ${failed} failed` : ''}.`;

            if (created > 0) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            console.error('Import error:', error);
            status.textContent = 'Error reading file: ' + error.message;
        }
    }

</script>

</body>
</html>