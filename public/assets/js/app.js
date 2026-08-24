/* Interaksi kecil shell: menu mobile dan dropdown user. */
(function () {
    'use strict';

    var toggle = document.getElementById('navToggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            document.body.classList.toggle('nav-open');
        });
        document.addEventListener('click', function (e) {
            if (document.body.classList.contains('nav-open')
                && !e.target.closest('.sidebar') && !e.target.closest('#navToggle')) {
                document.body.classList.remove('nav-open');
            }
        });
    }

    var dropdown = document.getElementById('userDropdown');
    var chip     = document.getElementById('userChipToggle');
    if (dropdown && chip) {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            dropdown.classList.toggle('is-open');
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#userDropdown')) {
                dropdown.classList.remove('is-open');
            }
        });
    }

    /* Input nominal: tampilkan pemisah ribuan saat mengetik, lalu kirim angka
       murni saat formulir disubmit. Server tetap membersihkannya sendiri,
       jadi mematikan JavaScript tidak membuat formulir gagal. */
    function groupThousands(digits) {
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatMoneyField(el) {
        var digits = el.value.replace(/[^0-9]/g, '');
        el.value = digits === '' ? '' : groupThousands(digits);
    }

    var moneyFields = document.querySelectorAll('[data-money]');
    moneyFields.forEach(function (el) {
        formatMoneyField(el);

        el.addEventListener('input', function () {
            /* Pertahankan posisi kursor relatif terhadap akhir teks, supaya
               titik yang disisipkan tidak melemparkan kursor ke ujung. */
            var fromEnd = el.value.length - el.selectionStart;
            formatMoneyField(el);
            var pos = Math.max(0, el.value.length - fromEnd);
            el.setSelectionRange(pos, pos);
        });
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-money]').forEach(function (el) {
                el.value = el.value.replace(/[^0-9]/g, '');
            });
        });
    });

    /* Dropdown bertingkat: sembunyikan pilihan yang bukan milik unit terpilih.
       Opsi disembunyikan lewat `hidden` DAN `disabled` — Safari mengabaikan
       `hidden` pada <option>, sedangkan `disabled` menghalangi pemilihannya
       di semua peramban. Penguncian sebenarnya tetap di sisi server. */
    document.querySelectorAll('[data-filter-by-unit]').forEach(function (select) {
        var unitSelect = document.querySelector(select.getAttribute('data-filter-by-unit'));
        if (!unitSelect) { return; }

        function terapkan() {
            var unitId = unitSelect.value;
            var terpilihMasihSah = false;

            Array.prototype.forEach.call(select.options, function (opt) {
                var unitOpsi = opt.getAttribute('data-unit');
                var cocok = !unitOpsi || unitOpsi === unitId;

                opt.hidden = !cocok;
                opt.disabled = !cocok;

                if (cocok && opt.selected) { terpilihMasihSah = true; }
            });

            /* Unit berganti sementara departemen lama sudah tidak berlaku —
               kosongkan, jangan biarkan menyimpan pasangan yang salah. */
            if (!terpilihMasihSah) { select.value = ''; }
        }

        unitSelect.addEventListener('change', terapkan);
        terapkan();
    });

    /* Konfirmasi untuk aksi yang tidak bisa dibatalkan. */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
})();
