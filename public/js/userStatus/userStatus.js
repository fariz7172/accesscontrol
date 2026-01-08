$(document).ready(function () {
    // Ambil URL dari window (diberikan oleh Blade)
    const latestUrl = window.userStatusConfig?.latestUrl || '/user-status/latest';

    let pollingInterval = 10000; // 10 detik

    // Ambil nilai awal dari URL (jika ada)
    const urlParams = new URLSearchParams(window.location.search);

    let startDate   = urlParams.get('start_date')   || $('#start_date').val()   || '';
    let endDate     = urlParams.get('end_date')     || $('#end_date').val()     || '';
    let department  = urlParams.get('department')  || $('#department').val()  || '';
    let branch      = urlParams.get('branch')      || $('#branch').val()      || '';
    let gate        = urlParams.get('gate')        || $('#gate').val()        || '';     // TAMBAHAN
    let search      = urlParams.get('search')      || '';
    let currentPage = urlParams.get('page')        || 1;

    let pollTimer;

    console.log('Polling started:', { startDate, endDate, department, branch, gate, search, currentPage });


    let searchTimeout;
        $('#search').on('keyup', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                $('#filterForm').trigger('submit');
            }, 600); // delay 600ms setelah berhenti ngetik
        });

    // ==================================================================
    // Update tabel & card dari response AJAX
    // ==================================================================
    function updateStatuses(data) {
        $('#total-in').text(data.total_in || 0);
        $('#total-out').text(data.total_out || 0);
        $('#total-entry-in').text(data.total_entry_in || 0);
        $('#total-entry-out').text(data.total_entry_out || 0);

        // === Tabel User Last Checked ===
        const tbody = $('#table-body');
        tbody.empty();

        if (data.userStatuses && data.userStatuses.length > 0) {
            data.userStatuses.forEach(status => {
                let badgeClass = 'bg-success';
                let textClass  = 'text-white';

                if (status.status_display === 'EXPIRED') {
                    badgeClass = 'bg-warning';
                    textClass  = 'text-dark fw-bold';
                } else if (status.status_display === 'OUT') {
                    badgeClass = 'bg-danger';
                }

                const row = `
                    <tr>
                        <td>${status.user_name || 'N/A'}</td>
                        <td>${status.last_event_formatted || '-'}</td>
                        <td><span class="badge ${badgeClass} ${textClass}">${status.status_display}</span></td>
                        <td>${status.dept_name || 'N/A'}</td>
                    </tr>`;
                tbody.append(row);
            });
        } else {
            tbody.append('<tr><td colspan="4" class="text-center">Tidak ada data untuk rentang tanggal ini.</td></tr>');
        }

        // === Tabel Log Data ===
        const logTbody = $('#log-body');
        logTbody.empty();

        if (data.allUserLogs && data.allUserLogs.length > 0) {
            data.allUserLogs.forEach(log => {
                let badgeClass = 'bg-success';
                let textClass  = 'text-white';

                if (log.status_display === 'EXPIRED') {
                    badgeClass = 'bg-warning';
                    textClass  = 'text-dark fw-bold';
                } else if (log.status_display === 'OUT') {
                    badgeClass = 'bg-danger';
                }

                const row = `
                    <tr>
                        <td>${log.user_name || 'N/A'}</td>
                        <td>${log.tm_event_formatted || '-'}</td>
                        <td>${log.device_name || 'N/A'}</td>
                        <td><span class="badge ${badgeClass} ${textClass}">${log.status_display}</span></td>
                        <td>${log.dept_name || 'N/A'}</td>
                    </tr>`;
                logTbody.append(row);
            });
        } else {
            logTbody.append('<tr><td colspan="5" class="text-center">Tidak ada log data untuk rentang tanggal ini.</td></tr>');
        }

        updatePaginationLinks(data.pagination);
    }

    // ==================================================================
    // Update link pagination
    // ==================================================================
    function updatePaginationLinks(pagination) {
        let html = '';
        if (pagination && pagination.last_page > 1) {
            html += '<nav><ul class="pagination justify-content-center">';

            // Previous
            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
            }

            // Nomor halaman
            for (let i = 1; i <= pagination.last_page; i++) {
                const active = i === pagination.current_page ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }

            // Next
            if (pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
            }

            html += '</ul></nav>';
        }
        $('#log-pagination').html(html);

        // Event klik pagination
        $('#log-pagination .page-link').off('click').on('click', function (e) {
            e.preventDefault();
            if ($(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) return;

            currentPage = $(this).data('page');
            pollLatestStatuses();
        });
    }

    // ==================================================================
    // Kirim request AJAX ke getLatestStatuses
    // ==================================================================
    function pollLatestStatuses() {
        $.ajax({
            url: latestUrl,
            method: 'GET',
            data: {
                start_date: startDate,
                end_date:   endDate,
                department: department,
                branch:     branch,
                gate:       gate,           // TAMBAHAN
                search:     search,
                page:       currentPage
            },
            success: function (response) {
                if (response.success === false) {
                    alert(response.message || 'Error dari server');
                    return;
                }
                updateStatuses(response);
            },
            error: function (xhr) {
                console.error('Polling error:', xhr.status, xhr.responseText);
            }
        });
    }

    // ==================================================================
    // Start polling otomatis
    // ==================================================================
    pollLatestStatuses();
    pollTimer = setInterval(pollLatestStatuses, pollingInterval);

    // ==================================================================
    // Handle submit form filter (termasuk gate)
    // ==================================================================
    $('#filterForm').on('submit', function (e) {
        e.preventDefault();

        // Ambil nilai terbaru dari form
        startDate   = $('#start_date').val();
        endDate     = $('#end_date').val();
        department  = $('#department').val() || '';
        branch      = $('#branch').val()     || '';
        gate        = $('#gate').val()       || '';        // TAMBAHAN
        search = $('#search').val().trim();    ;

        if (new Date(endDate) < new Date(startDate)) {
            alert('End date harus lebih besar atau sama dengan Start date.');
            return;
        }

        currentPage = 1; // reset ke halaman 1

        // Update URL tanpa reload
        const params = new URLSearchParams({
            start_date: startDate,
            end_date:   endDate,
            department: department,
            branch:     branch,
            gate:       gate,           // TAMBAHAN
            search:     search,
            page:       1
        });
        window.history.pushState({}, '', '?' + params.toString());

        pollLatestStatuses();
    });

    // ==================================================================
    // (Opsional) Auto-submit saat ubah dropdown/tanggal
    // ==================================================================
    $('#filterForm select, #filterForm input[type="date"]').on('change', function () {
        // Delay kecil biar user selesai pilih
        clearTimeout(window.filterTimeout);
        window.filterTimeout = setTimeout(() => {
            $('#filterForm').trigger('submit');
        }, 400);
    });

});