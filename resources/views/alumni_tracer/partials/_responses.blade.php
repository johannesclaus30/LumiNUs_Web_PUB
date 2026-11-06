<div class="tracer-panel" id="responses-panel">
    <div class="responses-toolbar">
        <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" class="search-input" placeholder="Search by name or program..." id="responsesSearch">
        </div>
        <select class="filter-select" id="responsesFilter">
            <option value="all">All Status</option>
            <option value="complete">Complete</option>
            <option value="in-progress">In Progress</option>
        </select>
        <button class="btn-export" onclick="exportResponses()">
            <i class="fa-solid fa-download"></i> Export CSV
        </button>
    </div>

    <div class="responses-table-container">
        <table class="tracer-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Alumni</th>
                    <th>Program</th>
                    <th>Year</th>
                    <th>Completion</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="responsesTableBody">
                <!-- Rendered by JS -->
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <span id="responsesCount">0 results</span>
    </div>
</div>

<script>
    // ============================================
    // RESPONSES
    // ============================================

    let allResponsesData = [];

    async function loadResponses() {
        if (!currentFormId) {
            renderResponsesTable([]);
            return;
        }

        try {
            const submissions = await apiFetch(`${API_BASE}/${currentFormId}/recent-submissions`);
            allResponsesData = Array.isArray(submissions) ? submissions : [];
            renderResponsesTable(allResponsesData);
            if (!Array.isArray(submissions)) {
                throw new Error(submissions?.error || 'Invalid submissions response.');
            }
        } catch (error) {
            console.error('Failed to load responses:', error);
            renderResponsesTable([]);
        }
    }

    function renderResponsesTable(data) {
        const tbody = document.getElementById('responsesTableBody');
        
        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--gray-400); padding: 3rem;">
                        <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.75rem;"></i>
                        No responses found.
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = data.map((r, i) => {
                const initials = r.alumni_name ? r.alumni_name.split(' ').map(n => n[0]).join('').toUpperCase() : '??';
                const responseId = r.id || i + 1;
                const isComplete = ['complete', 'completed'].includes(r.status);
                
                return `
                    <tr>
                        <td style="color:var(--gray-400);font-size:0.8125rem;">${responseId}</td>
                        <td>
                            <div class="alumni-info">
                                <div class="alumni-avatar">${initials}</div>
                                <span class="alumni-name">${r.alumni_name || 'Unknown'}</span>
                            </div>
                        </td>
                        <td>${r.program || 'N/A'}</td>
                        <td>${r.year_graduated || 'N/A'}</td>
                        <td>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar-track"><div class="progress-bar-fill" style="width:${r.completion}%; background:${r.completion === 100 ? '#10b981' : '#f59e0b'};"></div></div>
                                <span class="progress-text" style="color:${r.completion === 100 ? '#10b981' : '#f59e0b'};">${r.completion}%</span>
                            </div>
                        </td>
                        <td>${r.submitted_at || 'N/A'}</td>
                        <td><span class="status-badge ${isComplete ? 'completed' : 'in_progress'}">${isComplete ? 'Complete' : 'In Progress'}</span></td>
                        <td>
                            <div class="action-buttons">
                                <a href="/admin/alumni_tracer/response/${responseId}" class="btn-icon" title="View Response" style="text-decoration:none;">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button class="btn-icon delete" title="Delete" onclick="deleteResponse(${responseId})">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        document.getElementById('responsesCount').textContent = `${data.length} result${data.length !== 1 ? 's' : ''}`;
    }

    // Search & filter for responses
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('responsesSearch');
        const filterSelect = document.getElementById('responsesFilter');

        if (searchInput) {
            searchInput.addEventListener('input', filterResponses);
        }
        if (filterSelect) {
            filterSelect.addEventListener('change', filterResponses);
        }
    });

    async function filterResponses() {
        const query = (document.getElementById('responsesSearch')?.value || '').toLowerCase();
        const status = document.getElementById('responsesFilter')?.value || 'all';
        
        if (allResponsesData.length === 0 && currentFormId) {
            try {
                allResponsesData = await apiFetch(`${API_BASE}/${currentFormId}/recent-submissions`);
            } catch (e) {
                allResponsesData = [];
            }
        }
        
        const filtered = allResponsesData.filter(r => {
            const nameMatch = (r.alumni_name || '').toLowerCase().includes(query);
            const programMatch = (r.program || '').toLowerCase().includes(query);
            const normalizedStatus = ['complete', 'completed'].includes(r.status) ? 'complete' : 'in-progress';
            const statusMatch = status === 'all' || normalizedStatus === status;
            return (nameMatch || programMatch) && statusMatch;
        });
        
        renderResponsesTable(filtered);
    }

    /**
     * Delete a response
     */
    async function deleteResponse(responseId) {
        if (!confirm('Are you sure you want to delete this response?')) return;
        
        showLoading('Deleting response...', 'Please wait');
        
        try {
            const response = await apiFetch(`/admin/alumni_tracer/response/${responseId}`, {
                method: 'DELETE'
            });
            
            hideLoading();
            
            if (response.success) {
                showToast('Response deleted successfully!', 'success');
                await loadResponses();
            } else {
                showToast(response.message || 'Failed to delete response', 'error');
            }
        } catch (error) {
            hideLoading();
            showToast('Error deleting response: ' + error.message, 'error');
            console.error(error);
        }
    }

    /**
     * Export responses to CSV
     */
    function exportResponses() {
        if (!allResponsesData || allResponsesData.length === 0) {
            showToast('No data to export.', 'warning');
            return;
        }
        
        const headers = ['Alumni Name', 'Program', 'Year Graduated', 'Completion %', 'Status', 'Submitted At'];
        const rows = allResponsesData.map(r => [
            r.alumni_name || 'Unknown',
            r.program || 'N/A',
            r.year_graduated || 'N/A',
            r.completion || 0,
            ['complete', 'completed'].includes(r.status) ? 'Complete' : 'In Progress',
            r.submitted_at || 'N/A'
        ]);
        
        let csv = headers.join(',') + '\n';
        rows.forEach(row => {
            csv += row.join(',') + '\n';
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `tracer_responses_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        showToast('CSV exported successfully!', 'success');
    }
</script>

{{-- This is the _responses.blade.php --}}