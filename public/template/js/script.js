

    // document.addEventListener('DOMContentLoaded', function() {
    //     // Mendapatkan tanggal hari ini
    //     const today = new Date();
    //     const day = String(today.getDate()).padStart(2, '0');
    //     const month = String(today.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0, jadi ditambah 1
    //     const year = today.getFullYear();

    //     // Format tanggal menjadi YYYY-MM-DD
    //     const formattedDate = `${year}-${month}-${day}`;

    //     // Mengatur nilai input date dengan format YYYY-MM-DD
    //     document.getElementById('date_from').value = formattedDate;
    //     document.getElementById('date_to').value = formattedDate;

    //     var filterButton = document.getElementById('filter');
    //     var resetButton = document.getElementById('reset');

    //     // Fungsi untuk mengecek apakah URL memiliki query parameters
    //     function hasQueryParams() {
    //         return window.location.search.length > 0; // Cek apakah ada bagian query di URL
    //     }

    //     // Tampilkan tombol reset jika ada query parameters di URL
    //     if (hasQueryParams()) {
    //         resetButton.style.display = 'inline-block';
    //     }

    //     // Tambahkan event listener ke tombol Filter
    //     filterButton.addEventListener('click', function() {
    //         // Tampilkan tombol Reset setelah tombol Filter ditekan
    //         resetButton.style.display = 'inline-block';
    //     });

    //     // Tambahkan event listener ke tombol Reset
    //     resetButton.addEventListener('click', function() {
    //         // Sembunyikan tombol Reset saat tombol Reset ditekan
    //         resetButton.style.display = 'none';
    //     });
    // });




    // document.addEventListener('DOMContentLoaded', function() {
    //     var filterButton = document.getElementById('filter');
    //     var dateFromInput = document.getElementById('date_from');
    //     var dateToInput = document.getElementById('date_to');

    //     // Jika ada query parameters di URL, set nilai input fields
    //     function setInputValuesFromQueryParams() {
    //         var urlParams = new URLSearchParams(window.location.search);
    //         var dateFrom = urlParams.get('date_from');
    //         var dateTo = urlParams.get('date_to');

    //         if (dateFrom) {
    //             dateFromInput.value = dateFrom;
    //         }

    //         if (dateTo) {
    //             dateToInput.value = dateTo;
    //         }
    //     }

    //     // Set nilai input fields ketika halaman dimuat
    //     setInputValuesFromQueryParams();

    //     // Menangani klik pada tombol Filter
    //     filterButton.addEventListener('click', function() {
    //         // Mengupdate query parameters di URL secara otomatis saat form disubmit
    //     });
    // });
