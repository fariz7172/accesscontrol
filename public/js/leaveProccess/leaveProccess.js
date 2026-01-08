// public/js/leaveProccess/leaveProccess.js
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Tampilkan session message (success/error/validation)
    if (window.leaveProcessMessages) {
        const { success, error, validationErrors } = window.leaveProcessMessages;

        if (success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: success,
                confirmButtonText: 'OK'
            });
        }

        if (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error,
                confirmButtonText: 'OK'
            });
        }

        if (validationErrors && validationErrors.length > 0) {
            const list = validationErrors.map(err => `<li>${err}</li>`).join('');
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `<ul style="text-align:left">${list}</ul>`,
                confirmButtonText: 'OK'
            });
        }
    }

    // === UPDATE STATUS VIA AJAX ===
    document.querySelectorAll('.status-select').forEach(select => {
        // Simpan nilai awal
        select.dataset.originalValue = select.value;

        select.addEventListener('change', function () {
            const form = this.closest('.update-status-form');
            const leaveId = this.dataset.id;
            const newStatus = this.value;
            const statusText = this.options[this.selectedIndex].text;

            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi',
                text: `Ubah status menjadi "${statusText}"?`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Update!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            status: newStatus,
                            _method: 'PATCH'
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'Status berhasil diperbarui',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Gagal update status');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', err.message || 'Terjadi kesalahan', 'error');
                        this.value = this.dataset.originalValue;
                    });
                } else {
                    this.value = this.dataset.originalValue;
                }
            });
        });
    });

    // === DELETE CONFIRMATION ===
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Yakin Hapus?',
                text: 'Data cuti ini akan dihapus permanen!',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});