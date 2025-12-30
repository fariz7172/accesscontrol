$(document).ready(function() {
    // Cek URL saat ini
    var currentPath = window.location.pathname;

    // Sembunyikan semua sidebar items terlebih dahulu
    $('#userData').hide();
    $('#deviceData').hide();
    $('#logData').hide();
    $('#setting').hide();

    // Tampilkan elemen sidebar yang sesuai dengan URL saat ini
    if (currentPath.includes('/userAdmin')) {
        $('#userData').show(); // Menampilkan menu userData
    }
    if (currentPath.includes('/device')) {
        $('#deviceData').show(); // Menampilkan menu deviceData
    }
    if (currentPath.includes('/logData')) {
        $('#logData').show(); // Menampilkan menu logData
    }
    if (currentPath.includes('/setDatabase')) {
        $('#setting').show(); // Menampilkan menu setting
    }

    // Menyesuaikan tampilan berdasarkan status checkbox
    var isEeqChecked = localStorage.getItem('eeq') === 'true';
    var isDeviceDataChecked = localStorage.getItem('deviceDataID') === 'true';
    var isLogDataChecked = localStorage.getItem('logDataID') === 'true';
    var isSettingChecked = localStorage.getItem('settingID') === 'true';

    $('#eeq').prop('checked', isEeqChecked);
    $('#deviceDataID').prop('checked', isDeviceDataChecked);
    $('#logDataID').prop('checked', isLogDataChecked);
    $('#settingID').prop('checked', isSettingChecked);

    // Menyesuaikan tampilan berdasarkan status checkbox
    if (isEeqChecked) {
        toggleSidebar(document.getElementById('eeq'), 'userData');
    }
    if (isDeviceDataChecked) {
        toggleSidebar(document.getElementById('deviceDataID'), 'deviceData');
    }
    if (isLogDataChecked) {
        toggleSidebar(document.getElementById('logDataID'), 'logData');
    }
    if (isSettingChecked) {
        toggleSidebar(document.getElementById('settingID'), 'setting');
    }

    // Simpan status saat diubah
    $('#eeq').change(function() {
        localStorage.setItem('eeq', this.checked);
        toggleSidebar(this, 'userData');
    });

    $('#deviceDataID').change(function() {
        localStorage.setItem('deviceDataID', this.checked);
        toggleSidebar(this, 'deviceData');
    });

    $('#logDataID').change(function() {
        localStorage.setItem('logDataID', this.checked);
        toggleSidebar(this, 'logData');
    });

    $('#settingID').change(function() {
        localStorage.setItem('settingID', this.checked);
        toggleSidebar(this, 'setting');
    });
});

// Fungsi untuk menampilkan atau menyembunyikan sidebar
function toggleSidebar(checkbox, sidebarId) {
    if (checkbox.checked) {
        $('#' + sidebarId).show();
    } else {
        $('#' + sidebarId).hide();
    }
}