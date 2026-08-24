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

    /* Baris tabel yang dapat ditautkan: klik di mana saja membuka, dan bisa
       dijangkau keyboard (Tab lalu Enter/Spasi). Klik pada tautan/tombol di
       dalam baris tidak ikut memicu navigasi baris. */
    document.querySelectorAll('tr[data-href]').forEach(function (row) {
        var go = function () { window.location.href = row.getAttribute('data-href'); };
        row.style.cursor = 'pointer';
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, input, select, textarea, label, form')) return;
            go();
        });
        row.addEventListener('keydown', function (e) {
            if (e.target !== row) return;
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); }
        });
    });

    /* Master data klik-untuk-edit: tombol [data-edit="id"] menukar baris
       tampilan dengan baris edit (tr#edit-{id}). */
    document.querySelectorAll('[data-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-edit');
            var editRow = document.getElementById('edit-' + id);
            var viewRow = document.getElementById('view-' + id);
            if (!editRow) return;
            var open = editRow.classList.toggle('open');
            if (viewRow) viewRow.classList.toggle('editing', open);
            if (open) { var inp = editRow.querySelector('input,select,textarea'); if (inp) inp.focus(); }
        });
    });
    document.querySelectorAll('[data-cancel-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-cancel-edit');
            var editRow = document.getElementById('edit-' + id);
            var viewRow = document.getElementById('view-' + id);
            if (editRow) editRow.classList.remove('open');
            if (viewRow) viewRow.classList.remove('editing');
        });
    });

    /* Drag-and-drop urutan tahapan. Tiap tahap adalah satu <tbody> (berisi
       baris tampil + baris edit) agar keduanya ikut tergeser bersama. Urutan
       baru dikirim ke server; token CSRF diperbarui dari respons karena
       CI4 merotasinya tiap permintaan. */
    (function () {
        var table = document.getElementById('stageTable');
        if (!table) return;

        var dragged = null;

        table.querySelectorAll('tbody.drag-group').forEach(function (group) {
            var handle = group.querySelector('.drag-handle');
            if (!handle) return;

            // Aktifkan draggable hanya saat menekan pegangan.
            handle.addEventListener('mousedown', function () { group.setAttribute('draggable', 'true'); });
            handle.addEventListener('mouseup', function () { group.removeAttribute('draggable'); });

            group.addEventListener('dragstart', function (e) {
                dragged = group;
                group.style.opacity = '0.4';
                e.dataTransfer.effectAllowed = 'move';
            });
            group.addEventListener('dragend', function () {
                group.style.opacity = '';
                group.removeAttribute('draggable');
                dragged = null;
            });
            group.addEventListener('dragover', function (e) {
                e.preventDefault();
                if (!dragged || dragged === group) return;
                var rect = group.getBoundingClientRect();
                var after = (e.clientY - rect.top) > rect.height / 2;
                if (after) group.parentNode.insertBefore(dragged, group.nextSibling);
                else group.parentNode.insertBefore(dragged, group);
            });
            group.addEventListener('drop', function (e) { e.preventDefault(); simpanUrutan(); });
        });

        function nomorUlang() {
            var n = 1;
            table.querySelectorAll('tbody.drag-group .order-num').forEach(function (td) { td.textContent = n++; });
        }

        function simpanUrutan() {
            nomorUlang();
            var ids = Array.prototype.map.call(
                table.querySelectorAll('tbody.drag-group'),
                function (g) { return g.getAttribute('data-id'); }
            );

            var body = new URLSearchParams();
            ids.forEach(function (id) { body.append('order[]', id); });
            body.append(table.getAttribute('data-csrf-name'), table.getAttribute('data-csrf-hash'));

            var msg = document.getElementById('reorderMsg');
            fetch(table.getAttribute('data-reorder-url'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: body.toString(),
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.csrf) table.setAttribute('data-csrf-hash', j.csrf); // token baru utk geser berikutnya
                if (msg) {
                    msg.style.display = 'block';
                    msg.textContent = j.ok ? '✓ Urutan tersimpan.' : ('Gagal menyimpan urutan: ' + (j.msg || ''));
                    msg.style.color = j.ok ? 'var(--green)' : 'var(--red)';
                    if (j.ok) setTimeout(function () { msg.style.display = 'none'; }, 2000);
                }
            })
            .catch(function () {
                if (msg) { msg.style.display = 'block'; msg.style.color = 'var(--red)'; msg.textContent = 'Gagal menyimpan urutan.'; }
            });
        }
    })();

    /* Papan pipeline: afordance scroll untuk pengguna non-trackpad.
       - Gradasi + tombol panah menandakan & menggerakkan scroll horizontal.
       - Roda mouse (vertikal) diterjemahkan jadi geser horizontal saat di
         atas papan, sehingga bisa digeser tanpa trackpad. */
    document.querySelectorAll('.board-scroll').forEach(function (wrap) {
        var board = wrap.querySelector('.board');
        if (!board) return;

        function refresh() {
            var maxScroll = board.scrollWidth - board.clientWidth;
            wrap.classList.toggle('can-left', board.scrollLeft > 4);
            wrap.classList.toggle('can-right', board.scrollLeft < maxScroll - 4);
            if (board.scrollLeft > 4) wrap.classList.add('scrolled');
        }

        function geser(arah) {
            board.scrollBy({ left: arah * Math.max(260, board.clientWidth * 0.7), behavior: 'smooth' });
        }

        var prev = wrap.querySelector('.board-nav.prev');
        var next = wrap.querySelector('.board-nav.next');
        if (prev) prev.addEventListener('click', function () { geser(-1); });
        if (next) next.addEventListener('click', function () { geser(1); });

        board.addEventListener('scroll', refresh, { passive: true });
        window.addEventListener('resize', refresh);

        /* Roda mouse vertikal -> geser horizontal (hanya bila memang perlu). */
        board.addEventListener('wheel', function (e) {
            if (board.scrollWidth <= board.clientWidth) return;
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                board.scrollLeft += e.deltaY;
                e.preventDefault();
            }
        }, { passive: false });

        refresh();
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
