// public/js/deviceGroup/deviceGroup.js
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // URL dari route Laravel (akan di-inject dari Blade)
    const routes = window.deviceGroupRoutes;

    // Setup CSRF untuk semua AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    // === ADD FORM SUBMISSION ===
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: routes.store,
            method: 'POST',
            data: formData,
            success: function () {
                $('#addModal').modal('hide');
                Swal.fire('Success', 'Device Group berhasil ditambahkan!', 'success').then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors || { general: ['Terjadi kesalahan'] };
                let msg = '';
                $.each(errors, (key, value) => msg += value[0] + '<br>');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // === EDIT BUTTON CLICK ===
    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');

        $.ajax({
            url: routes.show.replace(':id', id), // route('deviceGroup.show', $id)
            method: 'GET',
            success: function (data) {
                $('#editId').val(data.id);
                $('#editNumber').val(data.number);
                $('#editName').val(data.name);
                $('#editDescription').val(data.description || '');

                const tbody = $('#editModal .device-gates-table');
                tbody.empty();

                // Gabungkan semua gate (yang sudah terpilih + yang tersedia)
                const allGates = [...data.device_gates, ...data.available_device_gates];
                const seen = new Set();

                allGates.forEach(gate => {
                    if (seen.has(gate.id)) return;
                    seen.add(gate.id);

                    const checked = data.device_gates.some(g => g.id === gate.id) ? 'checked' : '';
                    tbody.append(`
                        <tr>
                            <td><input type="checkbox" name="device_gates[]" value="${gate.id}" ${checked}></td>
                            <td>${gate.name || 'N/A'}</td>
                            <td>${gate.sn || 'N/A'}</td>
                        </tr>
                    `);
                });

                $('#editModal').modal('show');
            },
            error: function () {
                Swal.fire('Error', 'Gagal memuat data group', 'error');
            }
        });
    });

    // === EDIT FORM SUBMISSION ===
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#editId').val();
        const formData = $(this).serialize();

        $.ajax({
            url: routes.update.replace(':id', id), // route('deviceGroup.update', $id)
            method: 'PUT',
            data: formData,
            success: function () {
                $('#editModal').modal('hide');
                Swal.fire('Success', 'Device Group berhasil diperbarui!', 'success').then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors || { general: ['Gagal update'] };
                let msg = '';
                $.each(errors, (key, value) => msg += value[0] + '<br>');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // === DELETE BUTTON ===
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Yakin?',
            text: 'Group ini akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.destroy.replace(':id', id),
                    method: 'DELETE',
                    success: function () {
                        Swal.fire('Deleted!', 'Device Group telah dihapus.', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal menghapus group', 'error');
                    }
                });
            }
        });
    });
});