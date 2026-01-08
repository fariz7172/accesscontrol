


document.addEventListener('DOMContentLoaded', function () {
    // Filter toggle
    const filterToggle = document.getElementById('filterToggle'); // Checkbox
    const filterForm = document.getElementById('filterForm'); // Form Filter
    const excelForm = document.getElementById('excel'); // Form Export Excel
    const csvForm = document.getElementById('csv'); // Form Export CSV
    const pdfForm = document.getElementById('pdf'); // Form Export PDF
    const resetButton = document.getElementById('reset'); // Reset Button
    const searchForm = document.getElementById('searchForm'); // Search form
    const searchInput = searchForm.querySelector('input[name="search2"]'); // Search input
    const searchButton = searchForm.querySelector('button'); // Search button

    // Fungsi untuk menonaktifkan form dan tombol export
    function toggleDisableForm() {
        const isChecked = filterToggle.checked;

        // Menonaktifkan semua input dan tombol dalam filterForm
        filterForm.querySelectorAll('input, button').forEach(element => {
            element.disabled = isChecked;
        });

        // Menonaktifkan tombol export di dalam excelForm, csvForm, pdfForm
        excelForm.querySelectorAll('button').forEach(element => {
            element.disabled = isChecked;
        });
        csvForm.querySelectorAll('button').forEach(element => {
            element.disabled = isChecked;
        });
        pdfForm.querySelectorAll('button').forEach(element => {
            element.disabled = isChecked;
        });

        // Menonaktifkan tombol reset jika filter aktif
        resetButton.disabled = isChecked;
        resetButton.style.display = isChecked ? 'none' : 'inline-block';

        // Disable/Enable the search input and button based on checkbox
        searchInput.disabled = isChecked;
        searchButton.disabled = isChecked;
    }

    // Panggil fungsi pertama kali sesuai dengan status awal checkbox
    toggleDisableForm();

    // Event listener untuk checkbox change event
    filterToggle.addEventListener('change', toggleDisableForm);
    
    // Fungsi untuk mengambil data guestlog secara berkala
    function fetchGuestLogData() {
        const params = new URLSearchParams(window.location.search);

        $.ajax({
            url: '{{ route('guestlog.index') }}',
            method: 'GET',
            data: {
                date_from: params.get('date_from'),
                date_to: params.get('date_to'),
                model: params.get('model'),
                search2: params.get('search2'),
                page: params.get('page') // Menjaga agar nomor halaman tetap dipertahankan
            },
            success: function(response) {
                // Update data table dan pagination
                $('#data-table tbody').html(response.view);
                $('.pagination').html(response.pagination);

                // Update URL tanpa reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                history.replaceState(null, '', newUrl);
            },
            error: function(xhr) {
                console.error('Error fetching data:', xhr);
            }
        });
    }

    // Jalankan fetch data setiap 5 detik
    setInterval(fetchGuestLogData, 5000);

    // Fungsi untuk filter data
    function fetchFilteredData(page = 1, selectedModel = null) {
        const params = new URLSearchParams(window.location.search);
        $.ajax({
            url: '{{ route('guestlog.index') }}',
            method: 'GET',
            data: {
                date_from: params.get('date_from'),
                date_to: params.get('date_to'),
                search2: params.get('search2'),
                model: selectedModel,
                page: page // Pastikan page tetap dipertahankan saat filtering
            },
            success: function(response) {
                $('#data-table tbody').html(response.view);
                $('.pagination').html(response.pagination);

                // Update URL tanpa reload
                params.set('page', page);
                if (selectedModel) {
                    params.set('model', selectedModel);
                }
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                history.replaceState(null, '', newUrl);
            },
            error: function(xhr) {
                console.error('Error fetching data:', xhr);
            }
        });
    }

    // Ketika model dipilih
    document.getElementById('modelSelect').addEventListener('change', function() {
        const selectedModel = this.value;
        const formAction = document.getElementById('guestlogForm').action;
        const newUrl = `${formAction}?model=${selectedModel}`;

        window.location.href = newUrl;
    });
});
