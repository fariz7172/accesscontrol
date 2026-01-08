$(document).ready(function() {
    // Konfigurasi CSRF token untuk AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Variabel untuk melacak status proses
    let isProcessing = false;

    // Event untuk tombol Cek
    $('#checkMemberForm').on('submit', function(e) {
        e.preventDefault(); // Mencegah reload halaman
        console.log('Form submitted'); // Debugging

        if (isProcessing) {
            console.log('Process already running, ignoring submit');
            return; // Cegah klik berulang jika proses sedang berlangsung
        }

        // Set status proses ke true
        isProcessing = true;

        // Nonaktifkan tombol Cek dan tombol penutup modal
        $('#checkButton').prop('disabled', true);
        $('#closeModalBtn').prop('disabled', true);
        $('#modalCloseBtn').prop('disabled', true);

        // Tampilkan status proses dan progress bar
        $('#syncStatus').css('display', 'block');
        $('#syncProgressContainer').css('display', 'block');
        $('#syncProgressBar').css('width', '0%').attr('aria-valuenow', 0);
        $('#syncProgressText').text('0%');

        // Debugging: Periksa apakah elemen ada
        console.log('Progress container exists:', $('#syncProgressContainer').length);
        console.log('Progress bar exists:', $('#syncProgressBar').length);

        // Simulasi progress bar
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            if (progress <= 90) { // Hentikan di 90% sampai respons diterima
                $('#syncProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                $('#syncProgressText').text(Math.round(progress) + '%');
            }
        }, 500);

        // Panggil API untuk menyinkronkan semua data member
        $.ajax({
            url: '/api/sync-members',
            type: 'GET',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            timeout: 60000, // Timeout 60 detik
            success: function(response) {
                console.log('Sync response:', response);

                // Hentikan simulasi progress dan set ke 100%
                clearInterval(progressInterval);
                $('#syncProgressBar').css('width', '100%').attr('aria-valuenow', 100);
                $('#syncProgressText').text('100%');

                // Sembunyikan status proses dan progress bar setelah 1 detik
                setTimeout(() => {
                    $('#syncStatus').css('display', 'none');
                    $('#syncProgressContainer').css('display', 'none');
                }, 1000);

                // Tampilkan notifikasi hasil
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message || `Berhasil menyinkronkan ${response.total_synced} member dan mengirim data ke mesin.`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    resetModal();
                    // Refresh halaman setelah 1 detik
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                });
            },
            error: function(xhr, status, error) {
                // Hentikan simulasi progress
                clearInterval(progressInterval);

                let errorMessage = 'Gagal menyinkronkan data.';
                if (xhr.status === 503) {
                    errorMessage = 'Tidak ada perangkat yang dapat dijangkau. Periksa koneksi jaringan ke perangkat.';
                } else if (xhr.status === 404) {
                    errorMessage = xhr.responseJSON?.message || 'Tidak ada data ditemukan dari API.';
                } else if (xhr.status === 500) {
                    errorMessage = xhr.responseJSON?.message || 'Kesalahan server internal. Periksa log server untuk detail.';
                } else if (status === 'timeout') {
                    errorMessage = 'Permintaan ke API timeout. Pastikan server API merespons dengan cepat.';
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                console.error('Error details:', { status: xhr.status, response: xhr.responseText, error: error });

                // Sembunyikan status proses dan progress bar
                $('#syncStatus').css('display', 'none');
                $('#syncProgressContainer').css('display', 'none');

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                }).then(() => {
                    resetModal();
                });
            }
        });
    });

    // Mencegah penutupan modal saat proses berlangsung
    $('#successModalMember').on('hide.bs.modal', function(e) {
        if (isProcessing) {
            e.preventDefault();
            console.log('Modal close prevented due to ongoing process');
        }
    });

    // Fungsi untuk mengaktifkan kembali tombol dan modal
    function resetModal() {
        isProcessing = false;
        $('#checkButton').prop('disabled', false);
        $('#closeModalBtn').prop('disabled', false);
        $('#modalCloseBtn').prop('disabled', false);
    }
});