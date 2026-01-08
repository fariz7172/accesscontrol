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

        if (isProcessing) {
            return; // Cegah klik berulang jika proses sedang berlangsung
        }

        // Set status proses ke true
        isProcessing = true;

        // Nonaktifkan tombol Cek dan tombol penutup modal
        $('#checkButton').prop('disabled', true);
        $('#closeModalBtn').prop('disabled', true);
        $('#modalCloseBtn').prop('disabled', true);

        // Tampilkan status proses dan progress bar
        $('#syncStatus').show();
        $('#syncProgressContainer').show();
        $('#syncProgressBar').css('width', '0%');
        $('#syncProgressText').text('0%');

        // Ambil nomor member yang dipilih
        let selectedMemberNumbers = [];
        $('.user-checkbox:checked').each(function() {
            selectedMemberNumbers.push($(this).data('cardnumber'));
        });

       
        console.log('Selected member numbers:', selectedMemberNumbers);

        // Panggil API untuk mendapatkan data member
        $.ajax({
            url: syncMemberViewDreampos,
            type: 'GET',
            contentType: 'application/json',
            timeout: 10000,
            success: function(response) {
                console.log('API response:', response);
                let members = Array.isArray(response) ? response : (response && response.Number ? [response] : []);

                if (members.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'Tidak ada data member yang ditemukan dari API.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        resetModal();
                    });
                    return;
                }

                // Kirim data ke endpoint sync-members untuk insert/update
                processMembers(members);
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Gagal mengambil data dari API.';
                if (xhr.status === 0) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi jaringan atau pastikan server API berjalan.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint API tidak ditemukan (404). Pastikan URL benar: ' + syncMemberViewDreampos;
                } else if (xhr.status === 405) {
                    errorMessage = 'Metode HTTP tidak diizinkan (405). Server hanya menerima POST.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Kesalahan server internal (500). Periksa log server untuk detail.';
                } else if (status === 'timeout') {
                    errorMessage = 'Permintaan ke API timeout. Pastikan server API merespons dengan cepat.';
                } else if (xhr.responseJSON?.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                console.error('Error details:', { status: xhr.status, response: xhr.responseText, error: error });

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

    // Fungsi untuk memproses data member
    async function processMembers(members) {
        let completedRequests = 0;
        let failedRequests = 0;
        const totalRequests = members.length;

        // Kirim setiap member ke endpoint sync-members
        for (const member of members) {
            try {
                console.log('Processing member:', member.Number);
                const response = await $.ajax({
                    url: '/api/sync-members',
                    type: 'POST',
                    data: {
                        selected_member_numbers: [member.Number],
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                });

                console.log(`Member ${member.Number} synced successfully:`, response);
            } catch (err) {
                console.error(`Error syncing member ${member.Number}:`, err.responseText);
                failedRequests++;
            }

            completedRequests++;
            updateProgress(completedRequests, totalRequests);
            await delay(500);
        }

        // Sembunyikan status proses dan progress bar
        $('#syncStatus').hide();
        $('#syncProgressContainer').hide();

        // Tampilkan notifikasi hasil
        if (failedRequests > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: `Sinkronisasi selesai dengan ${failedRequests} kegagalan. Silakan periksa koneksi atau data.`,
                confirmButtonText: 'OK'
            }).then(() => {
                resetModal();
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: `Berhasil menyinkronkan ${totalRequests} member dan mengirim data ke mesin.`,
                confirmButtonText: 'OK'
            }).then(() => {
                resetModal();
            });
        }

        // Refresh halaman setelah 1 detik
        setTimeout(function() {
            location.reload();
        }, 1000);
    }

    // Fungsi untuk memperbarui progress bar
    function updateProgress(completed, total) {
        let progressPercentage = Math.min((completed / total) * 100, 100);
        $('#syncProgressBar').css('width', progressPercentage + '%').attr('aria-valuenow', progressPercentage);
        $('#syncProgressText').text(Math.round(progressPercentage) + '%');
    }

    // Fungsi untuk mengaktifkan kembali tombol dan modal
    function resetModal() {
        isProcessing = false;
        $('#checkButton').prop('disabled', false);
        $('#closeModalBtn').prop('disabled', false);
        $('#modalCloseBtn').prop('disabled', false);
    }

    // Fungsi untuk penundaan
    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
});