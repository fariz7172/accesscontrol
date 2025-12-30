// public/js/userAdmin/userAdmin.js
document.addEventListener('DOMContentLoaded', function () {
    const routes = window.userAdminRoutes;

    // === DELETE USER DENGAN SWEETALERT + AJAX ===
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.dataset.id;
            confirmDelete(userId);
        });
    });

    function confirmDelete(userId) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data user ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById(`deleteForm-${userId}`);
                if (!form) return console.error('Form tidak ditemukan!');

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'User berhasil dihapus.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            const row = document.getElementById(`userRow-${userId}`);
                            if (row) row.remove();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menghapus');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menghapus user. Silakan coba lagi.'
                    });
                });
            }
        });
    }

    // === TOGGLE ACCESS VIA AJAX ===
    window.toggleSidebar = function (checkbox, type) {
        const userId = checkbox.dataset.userid;
        const status = checkbox.checked ? 1 : 0;

        // Disable checkbox sementara
        checkbox.disabled = true;

        fetch(routes.updateAccess, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                type: type,
                status: status
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        })
        .catch(err => {
            console.error('Update access error:', err);
            checkbox.checked = !checkbox.checked; // rollback
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: err.responseJSON?.message || 'Tidak dapat mengubah akses.'
            });
        })
        .finally(() => {
            checkbox.disabled = false;
        });
    };

    // === SWEETALERT SUCCESS FLASH MESSAGE (dari session) ===
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Sukses!',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});