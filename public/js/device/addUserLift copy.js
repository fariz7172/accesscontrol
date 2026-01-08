////////////////////////////////////////////////////////////////////////////////
//  ADD USER TO LIFT – FINAL CLEAN VERSION (AJAX + Pagination + No Duplicate) //
////////////////////////////////////////////////////////////////////////////////

$(document).ready(function () {
    let searchTimeout = null;

    $('#perPageSelect').on('change', function() {
    loadUsers($('#searchUserDb').val().trim());
});


    // =====================================================================
    // 1. FUNGSI UTAMA (yang dipakai berulang-ulang)
    // =====================================================================
    function updateSelectedCount() {
        const userCount = $('.user-check-db:checked').length;
        const deviceCount = $('.device-check:checked').length;
        $('#selectedCount').text(userCount + ' user dipilih');
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
        const row = $(this).closest('tr');
        users.push({
            id: $(this).val(),
            name: $(this).data('name'),
            card: row.find('td').eq(3).text().trim(),
            beginDate: row.find('td').eq(4).data('begin') || null,
            endDate: row.find('td').eq(4).data('end') || null
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
        Swal.fire('Pilih user & device!', 'Minimal 1 user dan 1 device harus dipilih.', 'warning');
        return;
    }

    const lift = {
        lift1: parseInt($('#lift1_input').val() || 0),
        lift2: parseInt($('#lift2_input').val() || 0),
        lift3: parseInt($('#lift3_input').val() || 0),
        lift4: parseInt($('#lift4_input').val() || 0)
    };

    const totalTasks = users.length * devices.length;
    let completed = 0;
    let success = 0;
    let failed = 0;

    // TAMPILKAN PROGRESS BAR
    $('#progressContainer').show();
    $('#progressText').text('Sedang mengirim user ke mesin Soyal...');
    $('#progressCounter').text(`0 dari ${totalTasks}`);
    $('#progressBar').css('width', '0%').attr('aria-valuenow', 0);
    $('#progressPercent').text('0%');
    $('#successText').text('0 berhasil');
    $('#failedText').text('0 gagal');

    Swal.fire({
        title: 'Kirim ke Mesin Soyal?',
        text: `${users.length} user × ${devices.length} device = ${totalTasks} transaksi`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim Sekarang!'
    }).then(result => {
        if (!result.isConfirmed) {
            $('#progressContainer').hide();
            return;
        }

        const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');

        const MAX_CONCURRENT = 10;
        const tasks = [];

        users.forEach(user => {
            devices.forEach(device => {
                tasks.push({ user, device });
            });
        });

        let running = 0;
        let index = 0;

        function runNext() {
            while (running < MAX_CONCURRENT && index < tasks.length) {
                const task = tasks[index++];
                running++;

                (function(task) {
                    let cardNumber = task.user.card;
                    if (/^\d+$/.test(cardNumber)) {
                        const num = parseInt(cardNumber);
                        const high = (num >> 16) & 0xFFFF;
                        const low = num & 0xFFFF;
                        cardNumber = String(high).padStart(5, '0') + ':' + String(low).padStart(5, '0');
                    }

                    // PAKAI CARA LAMA — AMBIL WAKTU ASLI DARI DATA USER!
                    let beginDate = "01-01-2025", beginTime = "00:00";
                    let expireDate = "12-31-2030", expireTime = "23:59";

                    if (task.user.beginDate) {
                        const bd = new Date(task.user.beginDate);
                        beginDate = ("0" + (bd.getMonth() + 1)).slice(-2) + '-' +
                                    ("0" + bd.getDate()).slice(-2) + '-' + bd.getFullYear();
                        beginTime = ("0" + bd.getHours()).slice(-2) + ':' +
                                    ("0" + bd.getMinutes()).slice(-2);
                    }
                    if (task.user.endDate) {
                        const ed = new Date(task.user.endDate);
                        expireDate = ("0" + (ed.getMonth() + 1)).slice(-2) + '-' +
                                     ("0" + ed.getDate()).slice(-2) + '-' + ed.getFullYear();
                        expireTime = ("0" + ed.getHours()).slice(-2) + ':' +
                                     ("0" + ed.getMinutes()).slice(-2);
                    }

                    const payload = {
                        user_id: task.user.id,
                        user_name: task.user.name,
                        card_number: cardNumber,
                        ip_address: task.device.ip,
                        node_id: parseInt(task.device.node),
                        pin: "0",
                        type: "kartu",
                        timezone: "0",
                        begin_date: beginDate,     // SESUAI KODE LAMA
                        begin_time: beginTime,     // PAKAI WAKTU ASLI (bukan 00:00)
                        expire_date: expireDate,   // SESUAI KODE LAMA
                        expire_time: expireTime,   // PAKAI WAKTU ASLI (bukan 23:59)
                        lift1: lift.lift1,
                        lift2: lift.lift2,
                        lift3: lift.lift3,
                        lift4: lift.lift4
                    };

                    $.post('/api/soyal/book/v1/1/adduser', payload)
                        .done(() => {
                            success++;
                            $('#successText').text(`${success} berhasil`);

                            // Simpan relasi ke DB
                            $.post(window.Laravel.routes.saveUserToDevice, {
                                _token: window.Laravel.csrfToken,
                                user_id: task.user.id,
                                device_id: task.device.id,
                                Lift1: lift.lift1,
                                Lift2: lift.lift2,
                                Lift3: lift.lift3,
                                Lift4: lift.lift4
                            });
                        })
                        .fail(() => {
                            failed++;
                            $('#failedText').text(`${failed} gagal`);
                        })
                        .always(() => {
                            completed++;
                            running--;

                            const percent = Math.round((completed / totalTasks) * 100);
                            $('#progressCounter').text(`${completed} dari ${totalTasks}`);
                            $('#progressBar').css('width', percent + '%').attr('aria-valuenow', percent);
                            $('#progressPercent').text(percent + '%');

                            if (completed === totalTasks) {
                                const finalClass = failed === 0 ? 'bg-success' : 'bg-warning';
                                $('#progressBar').removeClass('bg-success bg-warning').addClass(finalClass);
                                $('#progressText').text(failed === 0 ? 'Semua berhasil!' : 'Selesai dengan beberapa gagal');

                                btn.prop('disabled', false).html('Kirim ke Mesin Soyal');
                                setTimeout(() => {
                                    $('#progressContainer').fadeOut();
                                    if (failed === 0) {
                                        $('#addUser').modal('hide');
                                        Swal.fire('Sukses Total!', 'Semua user berhasil dikirim!', 'success');
                                    }
                                }, 1500);
                            } else {
                                runNext();
                            }
                        });
                })(task);
            }
        }

        runNext();
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
            title: `<strong>Akses Lift: ${name}</strong>`,
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