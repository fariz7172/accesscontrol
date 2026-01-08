// public/js/device-lift-access.js
const AJAX_USERS_URL = '/device/ajax-users-for-lift'; // atau pakai route dari Laravel nanti

let selectedUser = null;
let selectedDevice = null;

function loadUsersLift(search = '') {
    $('#user-lift-table-body').html(`
        <tr>
            <td colspan="4" class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </td>
        </tr>
    `);

    $.get(AJAX_USERS_URL, { search: search })
        .done(function(data) {
            $('#user-lift-table-body').html(data);
        })
        .fail(function(xhr) {
            $('#user-lift-table-body').html(`
                <tr><td colspan="4" class="text-danger text-center">
                    Gagal memuat data!<br>
                    <small>${xhr.status} ${xhr.statusText}</small>
                </td></tr>
            `);
        });
}

// Event modal dibuka
$(document).on('shown.bs.modal', '#addUser', function () {
    loadUsersLift();
});

// Search
$(document).on('keyup', '#userSearch', function() {
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        loadUsersLift($(this).val());
    }, 500);
});

// Pilih user
$(document).on('click', '.user-row', function() {
    $('.user-row').removeClass('table-primary');
    $(this).addClass('table-primary');

    selectedUser = {
        id: $(this).data('user-id'),
        name: $(this).data('user-name'),
        card: $(this).data('user-card')
    };

    $('#selectedUserInfo').html(`<strong>${selectedUser.name}</strong><br><small>Kartu: ${selectedUser.card}</small>`);
    checkProceedButton();
});

// Pilih device
$(document).on('change', 'input[name="selected_device_id"]', function() {
    const $label = $(this).closest('label');
    selectedDevice = {
        id: $(this).val(),
        name: $label.find('.font-weight-bold').text().trim()
    };

    $('#selectedDeviceInfo').html(`<strong>${selectedDevice.name}</strong>`);
    checkProceedButton();
});

function checkProceedButton() {
    $('#proceedSetLift').prop('disabled', !(selectedUser && selectedDevice));
}

$(document).on('click', '#proceedSetLift', function() {
    alert(`User ID: ${selectedUser.id} akan ditambahkan ke Device ID: ${selectedDevice.id}`);
    // Nanti ganti dengan AJAX POST ke route add-user-lift
});