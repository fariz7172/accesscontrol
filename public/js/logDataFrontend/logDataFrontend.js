
            $(document).ready(function() {
                // Muat filter dari localStorage saat halaman dimuat
                function loadFiltersFromStorage() {
                    let dateFrom = localStorage.getItem('filter_date_from');
                    let dateTo = localStorage.getItem('filter_date_to');
                    let branchId = localStorage.getItem('filter_branch_id');
                    let depId = localStorage.getItem('filter_dep_id');
                    let search2 = localStorage.getItem('filter_search2');

                    if (dateFrom) $('#date_from').val(dateFrom);
                    if (dateTo) $('#date_to').val(dateTo);
                    if (branchId) $('#branch_id').val(branchId);
                    if (depId) $('#dep_id').val(depId);
                    if (search2) $('#searchForm input[name="search2"]').val(search2);
                }

                // Simpan filter ke localStorage saat form disubmit
                $('#dateFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    let dateFrom = $('#date_from').val();
                    let dateTo = $('#date_to').val();
                    let branchId = $('#branch_id').val();
                    let depId = $('#dep_id').val();
                    let search2 = $('#searchForm input[name="search2"]').val();

                    // Simpan filter ke localStorage
                    localStorage.setItem('filter_date_from', dateFrom);
                    localStorage.setItem('filter_date_to', dateTo);
                    localStorage.setItem('filter_branch_id', branchId);
                    localStorage.setItem('filter_dep_id', depId);
                    localStorage.setItem('filter_search2', search2);

                    // Perbarui URL dengan parameter filter
                    let url = '{{ route("logUserFrontend") }}' + '?date_from=' + encodeURIComponent(dateFrom) +
                              '&date_to=' + encodeURIComponent(dateTo) +
                              '&branch_id=' + encodeURIComponent(branchId) +
                              '&dep_id=' + encodeURIComponent(depId) +
                              '&search2=' + encodeURIComponent(search2);
                    window.location.href = url;
                });

                // Event listener untuk form pencarian
                $('#searchForm').on('submit', function(e) {
                    e.preventDefault();
                    let dateFrom = $('#date_from').val();
                    let dateTo = $('#date_to').val();
                    let branchId = $('#branch_id').val();
                    let depId = $('#dep_id').val();
                    let search2 = $(this).find('input[name="search2"]').val();

                    // Simpan filter ke localStorage
                    localStorage.setItem('filter_date_from', dateFrom);
                    localStorage.setItem('filter_date_to', dateTo);
                    localStorage.setItem('filter_branch_id', branchId);
                    localStorage.setItem('filter_dep_id', depId);
                    localStorage.setItem('filter_search2', search2);

                    // Perbarui URL dengan parameter filter
                    let url = '{{ route("logUserFrontend") }}' + '?date_from=' + encodeURIComponent(dateFrom) +
                              '&date_to=' + encodeURIComponent(dateTo) +
                              '&branch_id=' + encodeURIComponent(branchId) +
                              '&dep_id=' + encodeURIComponent(depId) +
                              '&search2=' + encodeURIComponent(search2);
                    window.location.href = url;
                });

                // Terapkan status dari localStorage ke baris yang ada
                $('#logTableBody tr').each(function() {
                    let userAddr = $(this).data('user-addr');
                    let tmEvent = $(this).data('timestamp');
                    let storedStatus = localStorage.getItem(`status_${userAddr}_${tmEvent}`);
                    if (storedStatus) {
                        let statusHtml = storedStatus === 'CARD EXPIRED' 
                            ? '<span class="text-danger">CARD EXPIRED</span>'
                            : storedStatus === 'CARD ACTIVE'
                            ? '<span class="text-success">CARD ACTIVE</span>'
                            : '<span>N/A</span>';
                        $(this).find('.status-cell').html(statusHtml);
                    }
                });

                // Fungsi untuk memuat ulang tabel berdasarkan filter
                function loadFilteredLogs(dateFrom, dateTo, branchId, depId, search2) {
                    $.ajax({
                        url: '{{ route("logUserFrontend") }}',
                        method: 'GET',
                        data: {
                            date_from: dateFrom,
                            date_to: dateTo,
                            branch_id: branchId,
                            dep_id: depId,
                            search2: search2
                        },
                        success: function(response) {
                            let tbody = $('#logTableBody');
                            tbody.empty();
                            let logData = $(response).find('#logTableBody').html();
                            tbody.html(logData);
                            let pagination = $(response).find('.pagination').html();
                            $('.d-flex.justify-content-center.mt-4').html(pagination);
                            console.log('Table updated with filtered data');

                            // Terapkan status dari localStorage ke baris baru
                            $('#logTableBody tr').each(function() {
                                let userAddr = $(this).data('user-addr');
                                let tmEvent = $(this).data('timestamp');
                                let storedStatus = localStorage.getItem(`status_${userAddr}_${tmEvent}`);
                                if (storedStatus) {
                                    let statusHtml = storedStatus === 'CARD EXPIRED' 
                                        ? '<span class="text-danger">CARD EXPIRED</span>'
                                        : storedStatus === 'CARD ACTIVE'
                                        ? '<span class="text-success">CARD ACTIVE</span>'
                                        : '<span>N/A</span>';
                                    $(this).find('.status-cell').html(statusHtml);
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading filtered logs:', error);
                        }
                    });
                }

                // Fungsi polling untuk log terbaru
                function pollLogs() {
                    let lastTimestamp = $('#logTableBody tr:first').data('timestamp') || '';
                    let dateFrom = $('#date_from').val() || localStorage.getItem('filter_date_from') || '{{ now()->toDateString() }}';
                    let dateTo = $('#date_to').val() || localStorage.getItem('filter_date_to') || '{{ now()->toDateString() }}';
                    let branchId = $('#branch_id').val() || localStorage.getItem('filter_branch_id') || '';
                    let depId = $('#dep_id').val() || localStorage.getItem('filter_dep_id') || '';
                    let search2 = $('#searchForm input[name="search2"]').val() || localStorage.getItem('filter_search2') || '';

                    console.log('Polling with Last Timestamp:', lastTimestamp);
                    console.log('Date From:', dateFrom, 'Date To:', dateTo, 'Branch ID:', branchId, 'Dep ID:', depId, 'Search:', search2);

                    $.ajax({
                        url: '{{ route("getLatestLogs1") }}',
                        method: 'GET',
                        data: {
                            last_timestamp: lastTimestamp,
                            date_from: dateFrom,
                            date_to: dateTo,
                            branch_id: branchId,
                            dep_id: depId,
                            search2: search2
                        },
                        success: function(response) {
                            console.log('New Logs Received:', response);
                            if (response.length > 0) {
                                response.forEach(function(log) {
                                    let status = log.stat == '1' 
                                        ? '<span class="text-danger">CARD EXPIRED</span>' 
                                        : (log.stat == '0' 
                                            ? '<span class="text-success">CARD ACTIVE</span>' 
                                            : '<span>N/A</span>');
                                    localStorage.setItem(`status_${log.USER_ADDR}_${log.TM_EVENT}`, log.stat == '1' ? 'CARD EXPIRED' : (log.stat == '0' ? 'CARD ACTIVE' : 'N/A'));

                                    let photo = log.user_profile && log.user_profile.photo ?
                                        `<img src="${log.user_profile.photo.startsWith('data:image') ? log.user_profile.photo : 'data:image/jpeg;base64,' + log.user_profile.photo}" 
                                              alt="User Photo" 
                                              style="max-width: 100px; max-height: 100px;">` :
                                        'No Image';

                                    let branchName = log.user_profile && log.user_profile.branch ? log.user_profile.branch.name : 'N/A';
                                    let departmentName = log.user_profile && log.user_profile.department ? log.user_profile.department.name : 'N/A';
                                    let deviceName = log.device_name || 'N/A';

                                    let rowCount = $('#logTableBody tr').length;
                                    let no = rowCount > 0 ? parseInt($('#logTableBody tr:first td:first').text()) + 1 : 1;

                                    let newRow = `
                                        <tr data-timestamp="${log.TM_EVENT}" data-user-addr="${log.USER_ADDR}">
                                            <td>${no}</td>
                                            <td>${log.user_profile?.NAME || 'N/A'}</td>
                                            <td>${branchName}</td>
                                            <td>${departmentName}</td>
                                            <td>${log.TM_EVENT}</td>
                                            <td class="status-cell">${status}</td>
                                            <td>${deviceName}</td>
                                            <td>${photo}</td>
                                        </tr>
                                    `;
                                    $('#logTableBody').prepend(newRow);

                                    // Tampilkan notifikasi pembaruan
                                    $('#updateNotification').removeClass('d-none').fadeIn();
                                    setTimeout(() => $('#updateNotification').fadeOut().addClass('d-none'), 3000);

                                    if ($('#logTableBody tr').length > 50) {
                                        $('#logTableBody tr:last').remove();
                                    }

                                    updateRowNumbers();
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Error polling logs:', xhr.status, status, error);
                            console.log('Response:', xhr.responseText);
                        },
                        complete: function() {
                            setTimeout(pollLogs, 10000); // Interval polling 10 detik
                        }
                    });
                }

                function updateRowNumbers() {
                    $('#logTableBody tr').each(function(index) {
                        $(this).find('td:first').text(index + 1);
                    });
                }

                // Panggil fungsi untuk memuat filter dari localStorage
                loadFiltersFromStorage();

                // Mulai polling
                pollLogs();
            });
    