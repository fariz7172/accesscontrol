////////////////////////////////////////////////////////////////////////////////
//  ADD USER TO LIFT – FINAL CLEAN VERSION (AJAX + Pagination + No Duplicate) //
////////////////////////////////////////////////////////////////////////////////

$(document).ready(function () {
    let searchTimeout = null;

    $('#perPageSelect').on('change', function() {
    loadUsers($('#searchUserDb').val().trim());
});

// ====== TOMBOL: PILIH SEMUA LANTAI YANG DIIZINKAN (SESUAI DEVICE) ======
$('#checkAllFloors').on('click', function () {
    $('.lift-floor-checkbox').each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop('checked', true);
        }
    });

    // Update nilai lift1, lift2, lift3, lift4
    ['lift1', 'lift2', 'lift3', 'lift4'].forEach(prefix => {
        calculateLiftValue(prefix);
    });
});

// ====== TOMBOL: HAPUS SEMUA PILIHAN LANTAI ======
$('#uncheckAllFloors').on('click', function () {
    $('.lift-floor-checkbox').prop('checked', false);

    ['lift1', 'lift2', 'lift3', 'lift4'].forEach(prefix => {
        $(`#${prefix}_value`).text('0');
        $(`#${prefix}_input`).val('0');
    });
});


    // =====================================================================
    // 1. FUNGSI UTAMA 
    // =====================================================================
    function updateSelectedCount() {
        const userCount = $('.user-check-db:checked').length;
        const deviceCount = $('.device-check:checked').length;
        $('#selectedCount').text(userCount + ' users selected');
        $('#btnKirimSoyal').prop('disabled', userCount === 0 || deviceCount === 0);
    }

    function resetAllLiftAccess() {
        ['lift1', 'lift2', 'lift3', 'lift4'].forEach(prefix => {
            $(`input[name="${prefix}_floors[]"]`)
                .prop('checked', false)
                .prop('disabled', false);
            $(`#${prefix}_value`).text('0');
            $(`#${prefix}_input`).val('0');
        });
    }

    function calculateLiftValue(prefix) {
        let total = 0;
        $(`input[name="${prefix}_floors[]"]:checked`).each(function () {
            total += parseInt(this.value);
        });
        $(`#${prefix}_value`).text(total);
        $(`#${prefix}_input`).val(total);
    }

    function setLiftValue(prefix, value) {
        $(`#${prefix}_input`).val(value);
        $(`#${prefix}_value`).text(value);
        $(`input[name="${prefix}_floors[]"]`).each(function () {
            const bit = parseInt(this.value);
            $(this).prop('checked', (value & bit) !== 0);
        });
    }

  
    function updateAllowedFloors() {
    const checkedDevices = $('.device-check:checked');
    if (checkedDevices.length === 0) {
        $('.lift-floor-checkbox').prop('disabled', false);
        $('.lift-floor-label').css({ opacity: 1, color: '', cursor: 'pointer' });
        return;
    }

    let allowed = new Set();
    checkedDevices.each(function () {
        try {
            // GANTI INI: dari .data('allowed-floors') → .attr('data-allowed-floors')
            const floors = JSON.parse($(this).attr('data-allowed-floors') || '[]');
            floors.forEach(f => allowed.add(parseInt(f)));
        } catch (e) {
            console.warn('Error parsing allowed floors:', e);
        }
    });

    $('.lift-floor-checkbox').each(function () {
        const floor = parseInt($(this).data('floor-number'));
        const disabled = !allowed.has(floor);
        $(this).prop('disabled', disabled).prop('checked', false);
        $(this).next('.lift-floor-label').css({
            opacity: disabled ? 0.4 : 1,
            color: disabled ? '#999' : '',
            cursor: disabled ? 'not-allowed' : 'pointer'
        });
    });
}

    // =====================================================================
    // 2. AJAX LOAD USER (Live Search + Pagination)
    // =====================================================================
  function loadUsers(search = '') {
    const perPage = $('#perPageSelect').val() || 5;
    $.get(window.Laravel.routes['device.users.lift-search'], { 
        search: search,
        per_page: perPage 
    })
    .done(function (html) {
        $('#userSearchResults').html(html);
        updateSelectedCount();
    })
    .fail(function () {
        $('#userSearchResults').html('<tr><td colspan="6" class="text-danger text-center py-4">Gagal memuat data</td></tr>');
    });
}

    $('#searchUserDb').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadUsers($(this).val().trim()), 400);
    });

    // Klik pagination link
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        const url = new URL(this.href);
        url.searchParams.set('search', $('#searchUserDb').val().trim());
        $.get(url.toString()).done(html => {
            $('#userSearchResults').html(html);
            updateSelectedCount();
        });
    });

    // =====================================================================
    // 3. MODAL EVENTS
    // =====================================================================
    $('#addUser').on('shown.bs.modal', function () {
        // Reset semua
        $('.user-check-db, .device-check, #selectAllDb').prop('checked', false);
        $('#searchUserDb').val('');
        resetAllLiftAccess();
        updateAllowedFloors();
        updateSelectedCount();

        $('#perPageSelect').val('10');
        // Load data pertama kali
        loadUsers();
    });

    $('#addUser').on('hidden.bs.modal', function () {
        $('.user-check-db, .device-check').prop('checked', false);
        updateSelectedCount();
    });

    // =====================================================================
    // 4. USER SELECTION
    // =====================================================================
    $(document).on('change', '#selectAllDb', function () {
        $('.user-check-db').prop('checked', this.checked);
        updateSelectedCount();
    });

    $(document).on('change', '.user-check-db', updateSelectedCount);

    $('#selectAllVisible').on('click', function () {
        $('.user-check-db').prop('checked', true);
        $('#selectAllDb').prop('checked', true);
        updateSelectedCount();
    });

    $('#deselectAll').on('click', function () {
        $('.user-check-db, #selectAllDb').prop('checked', false);
        updateSelectedCount();
    });

    // =====================================================================
    // 5. DEVICE SELECTION & LIFT FLOOR LOGIC
    // =====================================================================
    $(document).on('change', '.device-check', function () {
        const checked = $('.device-check:checked');

        if (checked.length === 0) {
            resetAllLiftAccess();
        } else {
            const last = checked.last()[0];
            setLiftValue('lift1', parseInt($(last).data('lift1') || 0));
            setLiftValue('lift2', parseInt($(last).data('lift2') || 0));
            setLiftValue('lift3', parseInt($(last).data('lift3') || 0));
            setLiftValue('lift4', parseInt($(last).data('lift4') || 0));
        }

        updateAllowedFloors();
        updateSelectedCount();
    });

    $(document).on('change', '.lift-floor-checkbox', function () {
        if ($(this).prop('disabled')) return;
        const prefix = this.name.split('_')[0];
        calculateLiftValue(prefix);
    });

    // =====================================================================
    // 6. KIRIM KE SOYAL (tetap sama, hanya sedikit rapihkan)
    // =====================================================================
$('#btnKirimSoyal').on('click', function () {
    const users = [];
    $('.user-check-db:checked').each(function () {
        const $cb = $(this);

        // Ambil data tanggal dari attribute data-begin & data-end
        const beginIso = $cb.data('begin');
        const endIso = $cb.data('end');

        const beginDate = beginIso ? new Date(beginIso) : new Date();
        const endDate = endIso ? new Date(endIso) : new Date('2030-12-31');

        // Format: MM-DD-YYYY
        const formatDate = (d) => {
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const yyyy = d.getFullYear();
            return `${mm}-${dd}-${yyyy}`;
        };

        // Format: HH:MM
        const formatTime = (d) => {
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        };

        let cardNumber = $cb.data('card') + '';
        if (/^\d+$/.test(cardNumber)) {
            const num = parseInt(cardNumber);
            const high = (num >> 16) & 0xFFFF;
            const low = num & 0xFFFF;
            cardNumber = String(high).padStart(5, '0') + ':' + String(low).padStart(5, '0');
        }

        users.push({
            id: $cb.val(),
            name: $cb.data('name'),
            card: cardNumber,
            begin_date: formatDate(beginDate),
            begin_time: formatTime(beginDate),
            expire_date: formatDate(endDate),
            expire_time: formatTime(endDate)
        });
    });

    const devices = [];
    $('.device-check:checked').each(function () {
        devices.push({
            id: $(this).data('id'),
            ip: $(this).data('ip'),
            node: $(this).data('node')
        });
    });

    if (users.length === 0 || devices.length === 0) {
        Swal.fire('Error', 'Pilih minimal 1 user dan 1 device!', 'warning');
        return;
    }

    const lift = {
        lift1: parseInt($('#lift1_input').val() || 0),
        lift2: parseInt($('#lift2_input').val() || 0),
        lift3: parseInt($('#lift3_input').val() || 0),
        lift4: parseInt($('#lift4_input').val() || 0)
    };

    const total = users.length * devices.length;
    let done = 0, success = 0, failed = 0;

    $('#progressContainer').show();
    $('#progressText').text('Send To Mechine Soyal...');
    $('#progressCounter').text(`0 dari ${total}`);
    $('#progressBar').css('width', '0%');
    $('#progressPercent').text('0%');
    $('#successText').html('0 Success');
    $('#failedText').html('0 Fail');

    Swal.fire({
        title: 'Send To Mechine Soyal?',
       text: `Will send ${users.length} user(s) to ${devices.length} device(s) (${total} transactions)`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Send Now!'
    }).then(res => {
        if (!res.isConfirmed) {
            $('#progressContainer').hide();
            return;
        }

        $(this).prop('disabled', true).html('Sending to Soyal device...');

        users.forEach(user => {
            devices.forEach(device => {
                const payload = {
                    user_id: user.id,
                    user_name: user.name,
                    card_number: user.card,
                    ip_address: device.ip,
                    node_id: device.node,
                    timezone: "0",
                    pin: "0",
                    type: "kartu",
                    begin_date: user.begin_date,    // dari BEGIN_DATE (tanggal saja)
                    begin_time: user.begin_time,    // dari BEGIN_DATE (jam:menit)
                    expire_date: user.expire_date,  // dari END_DATE (tanggal saja)
                    expire_time: user.expire_time,  // dari END_DATE (jam:menit)
                    lift1: lift.lift1,
                    lift2: lift.lift2,
                    lift3: lift.lift3,
                    lift4: lift.lift4,
                    _token: window.Laravel.csrfToken
                };

                $.post('/api/soyal/book/v1/1/adduser', payload)
                    .done(() => {
                        success++;
                        $.post(window.Laravel.routes.saveUserToDevice, {
                            _token: window.Laravel.csrfToken,
                            user_id: user.id,
                            device_id: device.id,
                            Lift1: lift.lift1,
                            Lift2: lift.lift2,
                            Lift3: lift.lift3,
                            Lift4: lift.lift4
                        });
                    })
                    .fail(() => failed++)
                    .always(() => {
                        done++;
                        const percent = Math.round((done / total) * 100);

                        $('#progressCounter').text(`${done} / ${total}`);
                        $('#progressPercent').text(percent + '%');
                        $('#successText').html(`${success} berhasil`);
                        $('#failedText').html(`${failed} gagal`);

                        const $bar = $('#progressBar');
                        $bar.removeClass('smooth');
                        void $bar[0].offsetWidth;
                        $bar.addClass('smooth').css('width', percent + '%');

                        if (done === total) {
                            Swal.fire('Finished!', 
                                failed === 0 
                                    ? 'All users successfully submitted!' 
                                    : `${failed} failed from ${total} transaction`, 
                                failed === 0 ? 'success' : 'warning'
                            );
                            $(this).prop('disabled', false).html('Send To Mechine Soyal');
                            setTimeout(() => $('#progressContainer').fadeOut(), 2000);
                            if (failed === 0) $('#addUser').modal('hide');
                        }
                    });
            });
        });
    });
});
    // =====================================================================
    // 7. POPOVER LIFT ACCESS DETAIL (tetap sama)
    // =====================================================================
    $(document).on('click', '.lift-access-detail', function (e) {
        e.preventDefault();
        const name = this.dataset.name;
        const access = JSON.parse(this.dataset.access || '[]');

        let content = '<div class="p-3 small">';
        if (access.length === 0) {
            content += '<em class="text-muted">Belum ada akses lift</em>';
        } else {
            access.forEach(acc => {
                content += `<div class="mb-2"><strong class="text-primary">${acc.device}</strong><br>`;
                acc.floors.forEach(f => {
                    content += `<span class="badge bg-info text-dark me-1">${f}</span>`;
                });
                content += '</div>';
            });
        }
        content += '</div>';

        if (this._popover) this._popover.dispose();
        this._popover = new bootstrap.Popover(this, {
            title: `<strong>Access Lift: ${name}</strong>`,
            content: content,
            html: true,
            placement: 'left',
            trigger: 'manual',
            customClass: 'lift-popover',
            container: 'body',
            sanitize: false
        });
        this._popover.show();

        setTimeout(() => this._popover && this._popover.hide(), 8000);
    });
});