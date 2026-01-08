document.addEventListener('DOMContentLoaded', function () {
    // === 1. Save Changes Button & Form Optimization ===
    const saveButton = document.getElementById('saveShiftCodesButton');
    const form = document.getElementById('shiftCodeUpdateForm');

    window.markForUpdate = function (input) {
        saveButton.disabled = false;
        input.classList.add('changed');
    };

    form.addEventListener('submit', function (e) {
        // Hapus name dari input yang tidak berubah (hemat data)
        document.querySelectorAll('.shift-code-input:not(.changed)').forEach(input => {
            input.removeAttribute('name');
        });

        // Reset class changed
        document.querySelectorAll('.changed').forEach(input => {
            input.classList.remove('changed');
        });

        // Disable tombol setelah submit
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
    });

    // === 2. Hover Tooltip Shift Details ===
    let currentCard = null;

    function showShiftCard(shiftCode, userId, date, x, y) {
        if (!shiftCode || shiftCode.trim() === '') return;

        // Ganti placeholder di route dengan kode shift
        let url = window.recapConfig.getShiftDetailsUrl.replace(':code', shiftCode);
        url += `?user_id=${userId}&date=${date}`;

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.recapConfig.csrfToken
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }

            removeShiftCard();

            const card = document.createElement('div');
            card.className = 'shift-card';
            card.style.left = (x + 10) + 'px';
            card.style.top = (y + 10) + 'px';

            card.innerHTML = `
                <div class="shift-card-header">Shift Details</div>
                <div class="shift-card-item"><strong>ID:</strong> ${data.id || '-'}</div>
                <div class="shift-card-item"><strong>Name:</strong> ${data.ShiftName || '-'}</div>
                <div class="shift-card-item"><strong>In:</strong> ${data.Begin_Time || '-'}</div>
                <div class="shift-card-item"><strong>Out:</strong> ${data.Out_time || '-'}</div>
                <div class="shift-card-item"><strong>Type:</strong> ${data.Type || '-'}</div>
            `;

            document.body.appendChild(card);
            currentCard = card;
        })
        .catch(err => {
            console.error('Fetch failed:', err);
        });
    }

    function removeShiftCard() {
        if (currentCard) {
            currentCard.remove();
            currentCard = null;
        }
    }

    // Event delegation untuk performa lebih baik
    document.addEventListener('mouseover', function (e) {
        if (!e.target.classList.contains('shift-code-input')) return;

        const input = e.target;
        const shiftCode = input.value.trim();
        const userId = input.dataset.userId;
        const date = input.dataset.date;

        if (shiftCode) {
            showShiftCard(shiftCode, userId, date, e.pageX, e.pageY);
        }
    });

    document.addEventListener('mouseout', function (e) {
        if (e.target.classList.contains('shift-code-input') || e.target.closest('.shift-card')) {
            // Delay sedikit agar user bisa pindah ke card
            setTimeout(() => {
                if (!document.querySelector('.shift-card:hover')) {
                    removeShiftCard();
                }
            }, 100);
        }
    });

    // Biar card tetap muncul saat mouse di atas card
    document.addEventListener('mouseover', function (e) {
        if (e.target.closest('.shift-card')) {
            clearTimeout(window.hideCardTimeout);
        }
    });

    document.addEventListener('mouseout', function (e) {
        if (e.target.closest('.shift-card')) {
            window.hideCardTimeout = setTimeout(removeShiftCard, 300);
        }
    });

    // === 3. Tampilkan detail pola shift di card (jika PatternID ada) ===
    const patternId = new URLSearchParams(window.location.search).get('PatternID');
    if (patternId) {
        fetch(`/shift-pattern/${patternId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.error) {
                document.getElementById('patternName').textContent = data.PatternName || 'Unknown Pattern';
            }
        })
        .catch(() => {});
    }
});