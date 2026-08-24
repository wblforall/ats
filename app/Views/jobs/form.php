<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('briefcase') ?></span>
            <?= $job ? 'Ubah Lowongan' : 'Lowongan Baru' ?>
        </h1>
        <p class="page-sub">
            <?= $job ? esc($job['code']) . ' · dibuat ' . tgl($job['created_at']) : 'Kode dan tautan publik dibuat otomatis saat disimpan.' ?>
        </p>
    </div>
    <div class="spacer"></div>
    <a href="<?= $job ? site_url('lowongan/' . $job['id']) : site_url('lowongan') ?>" class="btn btn-ghost">Batal</a>
</div>

<form method="post" action="<?= $job ? site_url('lowongan/' . $job['id'] . '/ubah') : site_url('lowongan') ?>">
    <?= csrf_field() ?>

    <div class="grid" style="grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); align-items:start;">
        <div>
            <div class="card">
                <div class="section-title" style="margin-top:0;">Informasi Posisi</div>

                <div class="form-row">
                    <label for="title">Nama Posisi <span style="color:var(--red)">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="<?= esc(old('title', $job['title'] ?? '')) ?>" placeholder="mis. Supervisor Housekeeping">
                </div>

                <div class="grid grid-2">
                    <div class="form-row">
                        <label for="unit_id">Unit Usaha <span style="color:var(--red)">*</span></label>
                        <select id="unit_id" name="unit_id" class="form-control" required <?= $job ? 'disabled' : '' ?>>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>" <?= (int) old('unit_id', $job['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>>
                                    <?= esc($unit['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($job): ?>
                            <input type="hidden" name="unit_id" value="<?= (int) $job['unit_id'] ?>">
                            <div class="hint">Unit tidak bisa dipindah setelah lowongan dibuat.</div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <label for="department_id">Departemen</label>
                        <?php
                        /* Semua departemen dirender sekaligus dengan penanda unitnya,
                           lalu disaring di sisi browser mengikuti unit yang dipilih.
                           Tanpa JavaScript, seluruh pilihan tetap terlihat — dan
                           pilihan lintas unit akan ditolak server saat disimpan. */
                        $departmentIdTerpilih = (int) old('department_id', $job['department_id'] ?? 0);
                        ?>
                        <select id="department_id" name="department_id" class="form-control"
                                data-filter-by-unit="#unit_id">
                            <option value="">— Tanpa departemen —</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" data-unit="<?= $dept['unit_id'] ?>"
                                        <?= $departmentIdTerpilih === (int) $dept['id'] ? 'selected' : '' ?>>
                                    <?= esc($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="hint">
                            Hanya departemen milik unit yang dipilih.
                            <a href="<?= site_url('master/departemen') ?>" target="_blank" rel="noopener">Kelola departemen</a>
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="employment_type">Jenis Hubungan Kerja <span style="color:var(--red)">*</span></label>
                        <select id="employment_type" name="employment_type" class="form-control" required>
                            <?php foreach (\App\Models\JobModel::EMPLOYMENT_TYPES as $key => $label): ?>
                                <option value="<?= $key ?>" <?= old('employment_type', $job['employment_type'] ?? 'full_time') === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="level">Jenjang</label>
                        <input type="text" id="level" name="level" class="form-control"
                               value="<?= esc(old('level', $job['level'] ?? '')) ?>" placeholder="mis. Staff, Supervisor">
                    </div>

                    <div class="form-row">
                        <label for="work_location">Lokasi Kerja</label>
                        <input type="text" id="work_location" name="work_location" class="form-control"
                               value="<?= esc(old('work_location', $job['work_location'] ?? '')) ?>" placeholder="Balikpapan">
                    </div>

                    <div class="form-row">
                        <label for="openings">Jumlah Kebutuhan <span style="color:var(--red)">*</span></label>
                        <input type="number" min="1" id="openings" name="openings" class="form-control" required
                               value="<?= esc(old('openings', $job['openings'] ?? 1)) ?>">
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:18px;">
                <div class="section-title" style="margin-top:0;">Isi Lowongan</div>
                <p class="page-sub" style="margin:-6px 0 14px;">Teks ini tampil apa adanya di portal karir. Tulis satu poin per baris.</p>

                <div class="form-row">
                    <label for="description">Deskripsi Pekerjaan</label>
                    <textarea id="description" name="description" class="form-control"><?= esc(old('description', $job['description'] ?? '')) ?></textarea>
                </div>

                <div class="form-row">
                    <label for="requirements">Kualifikasi</label>
                    <textarea id="requirements" name="requirements" class="form-control"><?= esc(old('requirements', $job['requirements'] ?? '')) ?></textarea>
                </div>

                <div class="form-row">
                    <label for="benefits">Yang Ditawarkan</label>
                    <textarea id="benefits" name="benefits" class="form-control"><?= esc(old('benefits', $job['benefits'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="section-title" style="margin-top:0;">Penayangan</div>

                <div class="form-row">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <?php foreach (\App\Models\JobModel::STATUSES as $key => $label): ?>
                            <option value="<?= $key ?>" <?= old('status', $job['status'] ?? 'draft') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Hanya status <b>Dibuka</b> yang tayang di portal karir.</div>
                </div>

                <div class="form-row">
                    <label for="closing_date">Tanggal Tutup</label>
                    <input type="date" id="closing_date" name="closing_date" class="form-control"
                           value="<?= esc(old('closing_date', $job['closing_date'] ?? '')) ?>">
                    <div class="hint">Kosongkan bila tanpa batas waktu.</div>
                </div>
            </div>

            <div class="card" style="margin-top:18px;">
                <div class="section-title" style="margin-top:0;">Gaji</div>

                <div class="form-row">
                    <label for="salary_min">Gaji Minimum (Rp)</label>
                    <input type="text" inputmode="numeric" data-money id="salary_min" name="salary_min" class="form-control"
                           value="<?= esc(nominal(old('salary_min', $job['salary_min'] ?? ''))) ?>" placeholder="4.000.000">
                </div>

                <div class="form-row">
                    <label for="salary_max">Gaji Maksimum (Rp)</label>
                    <input type="text" inputmode="numeric" data-money id="salary_max" name="salary_max" class="form-control"
                           value="<?= esc(nominal(old('salary_max', $job['salary_max'] ?? ''))) ?>" placeholder="6.000.000">
                </div>

                <?php
                /* Kotak centang yang tidak dicentang tidak ikut terkirim, sehingga
                   old() jatuh kembali ke nilai tersimpan dan centangnya menyala
                   lagi sendiri setelah gagal simpan. Input tersembunyi di bawah
                   ini memastikan kuncinya selalu ada di POST. */
                ?>
                <input type="hidden" name="salary_visible" value="0">
                <label style="display:flex; gap:9px; align-items:flex-start; font-size:13px; font-weight:500;">
                    <input type="checkbox" name="salary_visible" value="1" style="margin-top:3px;"
                           <?= old('salary_visible', $job['salary_visible'] ?? 0) ? 'checked' : '' ?>>
                    <span>Tampilkan kisaran gaji di portal karir</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:18px;">
                <?= icon('check') ?> <?= $job ? 'Simpan Perubahan' : 'Buat Lowongan' ?>
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>
