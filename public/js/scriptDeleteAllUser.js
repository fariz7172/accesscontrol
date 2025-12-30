$(document).ready(function() {
    // Fungsi untuk mengaktifkan semua checkbox ketika defaultCheck1 diubah
    $('#defaultCheck1').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#dataTable tbody').find('input[type="checkbox"][id="userCheckBox"]').prop('checked', isChecked);
    });

    // Fungsi untuk menghapus semua pengguna yang dipilih saat DeleteAllUser diklik
    $('#DeleteAllUser').on('click', function(e) {
        e.preventDefault();

        // Kumpulkan semua ID pengguna yang dipilih
        var selectedUserIds = [];
        $('#dataTable tbody').find('input[type="checkbox"][id="userCheckBox"]:checked').each(function() {
            selectedUserIds.push($(this).closest('tr').find('td:first').text());
        });

        // Jika tidak ada pengguna yang dipilih, tampilkan pesan
        if (selectedUserIds.length === 0) {
            alert('Pilih setidaknya satu pengguna untuk dihapus.');
            return;
        }

        // Kirim permintaan AJAX untuk menghapus data secara bulk
        $.ajax({
            url: '{{ route("delete.bulk.users") }}', // Pastikan route ini di-update di backend
            method: 'DELETE',
            data: {
                user_ids: selectedUserIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.success || response.error);

                // Reload halaman setelah penghapusan berhasil
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON.error || 'Terjadi kesalahan saat menghapus pengguna.');
            }
        });
    });
});
