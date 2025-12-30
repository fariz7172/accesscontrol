
$(document).ready(function() {
    // Event: saat modal Add Device dibuka
    $('#addModal').on('shown.bs.modal', function() {
        // Selalu sembunyikan dulu
        $('#liftSettings').addClass('d-none');

        // Cek tipe device
        if ($('#deviceType').val() == '1') {
            $('#liftSettings').removeClass('d-none').hide().slideDown(300);
        }

        // Pasang event change
        $('#deviceType').off('change.lift').on('change.lift', function() {
            if (this.value == '1') {
                $('#liftSettings').removeClass('d-none').hide().slideDown(300);
            } else {
                $('#liftSettings').slideUp(300, function() {
                    $(this).addClass('d-none');
                });
            }
        });

        // Reset nilai lift ke 0
        ['lift1', 'lift2', 'lift3', 'lift4'].forEach(function(prefix) {
            calculateLiftValue(prefix);
        });
    });

    // Optional: sembunyikan lagi saat modal ditutup
    $('#addModal').on('hidden.bs.modal', function() {
        $('#liftSettings').addClass('d-none');
    });
});