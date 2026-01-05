$(document).ready(function() {
    // Handle edit modal
    // REBOOT MODAL - Isi SN saat modal dibuka
$('#rebootModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const deviceSn = button.data('device-sn');

    $('#reboot-sn').val(deviceSn);
    $('#reboot-sn-display').text(deviceSn || 'Unknown');
});

// SET TIME MODAL - Isi SN juga (kalau kamu punya tombol Set Time)
$('#setTimeModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const deviceSn = button.data('device-sn');

    $('#set-time-sn').val(deviceSn);
    $('#set-time-sn-display').text(deviceSn || 'Unknown');
});


    $('#editModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var editUrl = '{{ route("device.edit", ":id") }}'.replace(':id', id);

        $.ajax({
            url: editUrl,
            type: 'GET',
            success: function(response) {
                if (!response.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while retrieving data.'
                    });
                    return;
                }

                let device = response.device;


                $('#edit-id').val(device.id);
                $('#edit-name').val(device.name);
                $('#edit-number').val(device.number);
                $('#edit-flagstatus').val(device.flagstatus);
                $('#edit-sn').val(device.sn);
                $('#edit-ip').val(device.ip);
                $('#edit-nodeid').val(device.nodeid);
                $('#edit-description').val(device.description);
                $('#edit-type').val(device.type);
                $('#edit-stat').val(device.stat);
                $('#editForm').attr('action', '{{ route("device.update", ":id") }}'.replace(
                    ':id', device.id));
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to load device data.'
                });
            }
        });
    });

    // Handle edit form submission
    $('#editForm').on('submit', function(event) {
        event.preventDefault();

        let id = $('#edit-id').val();
        let formData = $(this).serialize();

        $.ajax({
            url: '{{ route("device.update", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message ||
                            'An error occurred while updating the device.'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message ||
                        'Failed to update device. Please try again.'
                });
            }
        });
    });

    // Handle open device form submission
// === PING UNTUK SOYAL (type == 1) ===
    $('.check-connection-form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const button = form.find('button[type="submit"]');
        const deviceType = form.data('device-type');
        const deviceIp = form.data('ip');
        const deviceSn = form.data('device-sn');

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking...');

        if (deviceType == 1) {
            // Soyal → pakai ping
            $.post(window.Laravel.routes.ping, {
                _token: window.Laravel.csrfToken,
                ip: deviceIp
            })
            .done(function(res) {
                Swal.fire({
                    icon: res.success ? 'success' : 'warning',
                    title: res.success ? 'Connected' : 'Not Connected',
                    text: res.message,
                    timer: 3000
                }).then(() => {
                    location.reload();
                });
            })
            .fail(function() {
                Swal.fire('Error', 'Gagal menghubungi server', 'error');
            })
            .always(function() {
                button.prop('disabled', false).html('Check Connection');
            });

        } else {
            // Non-Soyal → pakai checkConnection biasa
            $.post(window.Laravel.routes.checkConnection, {
                _token: window.Laravel.csrfToken,
                sn: deviceSn
            })
            .done(function(res) {
                Swal.fire({
                    icon: res.success ? 'success' : 'error',
                    title: res.success ? 'Connected' : 'Not Connected',
                    text: res.message
                }).then(() => {
                    location.reload();
                });
            })
            .fail(function() {
                Swal.fire('Error', 'Gagal check koneksi', 'error');
            })
            .always(function() {
                button.prop('disabled', false).html('Check Connection');
            });
        }
    });

    // === SET TIME ===
    $('#setTimeForm').on('submit', function(e) {
        e.preventDefault();
        const timeVal = $('#set-time-time').val().replace('T', ' ');
        const finalTime = timeVal.endsWith(':') ? timeVal + '00' : timeVal;

        $.post(window.Laravel.routes.setTime, {
            _token: window.Laravel.csrfToken,
            sn: $('#set-time-sn').val(),
            time: finalTime
        })
        .done(function(res) {
            Swal.fire(res.success ? 'Success' : 'Error', res.message, res.success ? 'success' : 'error');
            if (res.success) $('#setTimeModal').modal('hide');
        })
        .fail(function() {
            Swal.fire('Error', 'Gagal set waktu', 'error');
        });
    });

    // === REBOOT ===
    $('#rebootForm').on('submit', function(e) {
        e.preventDefault();

        $.post(window.Laravel.routes.reboot, {
            _token: window.Laravel.csrfToken,
            sn: $('#reboot-sn').val()
        })
        .done(function(res) {
            Swal.fire(res.success ? 'Success' : 'Error', res.message, res.success ? 'success' : 'error');
            if (res.success) $('#rebootModal').modal('hide');
        })
        .fail(function() {
            Swal.fire('Error', 'Gagal reboot device', 'error');
        });
    });

    // === OPEN DOOR (Soyal TCP tetap pakai form biasa) ===
   // === OPEN DOOR – FULL AJAX UNTUK SEMUA DEVICE ===
$('.open-device-form').on('submit', function(e) {
    e.preventDefault(); // SELALU prevent default!

    const form = $(this);
    const button = form.find('button[type="submit"]');
    const url = form.attr('action');
    const isSoyal = form.data('device-type') == 1;

    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Opening...');

    $.ajax({
        url: url,
        type: 'POST',
        data: form.serialize(),
        success: function(res) {
            // Laravel biasanya return JSON atau redirect
            if (res.success !== undefined) {
                // Jika controller return JSON
                Swal.fire({
                    icon: res.success ? 'success' : 'error',
                    title: res.success ? 'Success' : 'Failed',
                    text: res.message || (res.success ? 'Door opened!' : 'Failed to open door')
                });
            } else {
                // Jika controller return redirect / HTML → anggap sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Door opened successfully!'
                });
            }
        },
        error: function(xhr) {
            let msg = 'Failed to open door';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                msg = xhr.responseText.substring(0, 150);
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: msg
            });
        },
        complete: function() {
            button.prop('disabled', false).html('Open');
        }
    });
});
});


