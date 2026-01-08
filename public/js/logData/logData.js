document.addEventListener('DOMContentLoaded', function () {
    const $ = window.jQuery;
    const routes = window.logDataRoutes;
    const todayStr = new Date().toISOString().split('T')[0];

    // Ambil filter dari URL (selalu prioritas tertinggi)
    function getCurrentFilters() {
        const params = new URLSearchParams(window.location.search);
        return {
            date_from:   params.get('date_from') || todayStr,
            date_to:     params.get('date_to')   || todayStr,
            branch_id:   params.get('branch_id') || '',
            dep_id:      params.get('dep_id')    || '',
            device_name: params.get('device_name') || ''
        };
    }

    // Simpan hanya branch, department, device ke localStorage (bukan tanggal!)
    function saveNonDateFilters() {
        localStorage.setItem('filter_branch_id',   $('#branch_id').val()   || '');
        localStorage.setItem('filter_dep_id',      $('#dep_id').val()      || '');
        localStorage.setItem('filter_device_name', $('#device_name').val() || '');
    }

    // Muat filter non-date dari localStorage saat pertama kali buka (hanya jika URL kosong)
    function loadNonDateFiltersIfEmpty() {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('branch_id') && !params.has('dep_id') && !params.has('device_name')) {
            $('#branch_id').val(localStorage.getItem('filter_branch_id') || '');
            $('#dep_id').val(localStorage.getItem('filter_dep_id') || '');
            $('#device_name').val(localStorage.getItem('filter_device_name') || '');
        }
    }

    loadNonDateFiltersIfEmpty();

    // Submit filter
    $('#dateFilterForm').on('submit', function (e) {
        e.preventDefault();
        saveNonDateFilters();
        const params = new URLSearchParams($(this).serialize());
        window.location.href = routes.index + '?' + params.toString();
    });

    // Tombol Today
    $('#todayBtn').on('click', function () {
        const params = new URLSearchParams({
            date_from: todayStr,
            date_to:   todayStr,
            branch_id: $('#branch_id').val() || '',
            dep_id:    $('#dep_id').val() || '',
            device_name: $('#device_name').val() || ''
        });
        saveNonDateFilters();
        window.location.href = routes.index + '?' + params.toString();
    });

    // Export Excel
    $('#exportExcel').on('click', function (e) {
        e.preventDefault();
        saveNonDateFilters();
        const filters = getCurrentFilters();
        const params = new URLSearchParams(filters);
        window.location.href = routes.export + '?' + params.toString();
    });

    // Update nomor urut
    function updateRowNumbers() {
        $('#logTableBody tr').each(function (i) {
            $(this).find('td:first').text(i + 1);
        });
    }

    // Polling (selalu pakai filter dari URL!)
    function pollLogs() {
        const filters = getCurrentFilters();
        const lastTimestamp = $('#logTableBody tr:first').data('timestamp') || '';

        const params = new URLSearchParams({
            last_timestamp: lastTimestamp,
            ...filters
        });

        fetch(routes.latestLogs + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(logs => {
            if (logs.length > 0) {
                logs.forEach(log => {
                    const statusText = log.card_status || 'N/A';
                    const statusClass = statusText === 'CARD EXPIRED' ? 'text-danger' :
                                       statusText === 'CARD ACTIVE' ? 'text-success' : '';
                    const photoSrc = log.user_profile?.photo
                        ? (log.user_profile.photo.startsWith('data:image')
                            ? log.user_profile.photo
                            : 'data:image/jpeg;base64,' + log.user_profile.photo)
                        : '';
                    const photoHtml = photoSrc ? `<img src="${photoSrc}" style="max-width:100px;max-height:100px;">` : 'No Image';

                    const newRow = `
                        <tr data-timestamp="${log.TM_EVENT}" data-user-addr="${log.USER_ADDR}">
                            <td></td>
                            <td>${log.user_profile?.NAME || 'N/A'}</td>
                            <td>${log.user_profile?.branch?.name || 'N/A'}</td>
                            <td>${log.user_profile?.department?.name || 'N/A'}</td>
                            <td>${log.TM_EVENT}</td>
                            <td class="status-cell"><span class="${statusClass}">${statusText}</span></td>
                            <td>${log.device_name || 'N/A'}</td>
                            <td>${log.user_profile?.Card || 'N/A'}</td>
                            <td>${photoHtml}</td>
                        </tr>`;
                    $('#logTableBody').prepend(newRow);
                });

                // Batasi hanya 50 baris teratas
                while ($('#logTableBody tr').length > 50) {
                    $('#logTableBody tr:last').remove();
                }

                updateRowNumbers();
                $('#updateNotification').removeClass('d-none').fadeIn(300).delay(3000).fadeOut(300);
            }
        })
        .catch(err => console.error('Polling error:', err));

        // Update jumlah Employee & Member
        fetch(routes.getCounts)
            .then(r => r.json())
            .then(data => {
                if (data.employeeCount !== undefined) {
                    $('[data-count="employee"]').text(data.employeeCount);
                }
                if (data.memberCount !== undefined) {
                    $('[data-count="member"]').text(data.memberCount);
                }
            });

        setTimeout(pollLogs, 10000); // setiap 10 detik
    }

    // Jalankan polling
    pollLogs();
});