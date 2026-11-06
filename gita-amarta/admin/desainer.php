<?php
/**
 * Edit data desainer untuk section "Tentang Desainer" di beranda:
 * foto, nama, peran, keterangan (bio), kutipan, dan tampil/sembunyikan section.
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

$lama  = getDesainer();
$error = [];
$data  = $lama ?? [
    'id_desainer' => 0,
    'nama'        => '',
    'peran'       => '',
    'bio'         => '',
    'kutipan'     => '',
    'foto'        => null,
    'status'      => 'aktif',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $input = ambilInputDesainer($_POST);
    $error = validasiDesainer($input);

    // Foto hanya diproses jika isian lain sudah benar, agar tidak ada file yatim.
    $foto = $data['foto'];
    if (!$error) {
        $unggah = unggahGambar($_FILES['foto'] ?? null);
        if ($unggah['error'] !== null) {
            $error['foto'] = $unggah['error'];
        } elseif ($unggah['nama'] !== null) {
            $foto = $unggah['nama'];
        }
    }

    if (!$error) {
        simpanDesainer((int) $data['id_desainer'], $input, $foto);
        if ($foto !== $data['foto']) {
            hapusGambarJikaTidakDipakai($data['foto']);
        }

        setFlash('success', $input['status'] === 'aktif'
            ? 'Data desainer sudah disimpan dan langsung tampil di beranda.'
            : 'Data desainer sudah disimpan. Section "Tentang Desainer" sedang disembunyikan dari beranda.');
        redirect('desainer.php');
    }

    $data = array_merge($data, $input, ['status' => $input['status'] !== '' ? $input['status'] : 'aktif']);
}

$judulHalaman = 'Desainer';
$menuAktif    = 'desainer';

require __DIR__ . '/includes/atas.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Data belum bisa disimpan. Periksa kolom yang ditandai merah.
    </div>
<?php endif; ?>

<p class="text-body-secondary">Data ini tampil di section <strong>Tentang Desainer</strong> pada beranda website.</p>

<form action="desainer.php" method="post" enctype="multipart/form-data">
    <?= inputCsrf() ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Foto desainer</h2></div>
                <div class="card-body">
                    <img src="<?= e(urlGambarAdmin($data['foto'])) ?>" alt="Pratinjau foto desainer"
                         class="img-preview img-preview--potret mb-3" data-preview-untuk="foto" width="600" height="720">
                    <label for="foto" class="form-label fw-semibold">Ganti foto <span class="text-body-secondary fw-normal">(opsional)</span></label>
                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                           class="form-control<?= kelasError($error, 'foto') ?>">
                    <div class="form-text">JPG, PNG, atau WEBP, maksimal 2 MB. Pakai foto <strong>potret</strong> (tegak, rasio 5:6). Bagian tepi akan dipotong otomatis.</div>
                    <?= pesanError($error, 'foto') ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Keterangan</h2></div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="nama" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" id="nama" name="nama" maxlength="100" required
                                   class="form-control<?= kelasError($error, 'nama') ?>" value="<?= e($data['nama']) ?>">
                            <?= pesanError($error, 'nama') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="peran" class="form-label fw-semibold">Peran / jabatan <span class="text-danger">*</span></label>
                            <input type="text" id="peran" name="peran" maxlength="100" required placeholder="Contoh: Pendiri & Desainer Grafis"
                                   class="form-control<?= kelasError($error, 'peran') ?>" value="<?= e($data['peran']) ?>">
                            <?= pesanError($error, 'peran') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label fw-semibold">Keterangan singkat <span class="text-danger">*</span></label>
                        <textarea id="bio" name="bio" rows="4" maxlength="600" required
                                  class="form-control<?= kelasError($error, 'bio') ?>"><?= e($data['bio']) ?></textarea>
                        <div class="form-text">2–3 kalimat perkenalan, maksimal 600 karakter.</div>
                        <?= pesanError($error, 'bio') ?>
                    </div>

                    <div>
                        <label for="kutipan" class="form-label fw-semibold">Kutipan <span class="text-body-secondary fw-normal">(opsional)</span></label>
                        <input type="text" id="kutipan" name="kutipan" maxlength="255"
                               class="form-control<?= kelasError($error, 'kutipan') ?>" value="<?= e($data['kutipan']) ?>">
                        <div class="form-text">Tanpa tanda kutip, akan ditambahkan otomatis. Kosongkan jika tidak ingin menampilkan kutipan.</div>
                        <?= pesanError($error, 'kutipan') ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Tampilkan di beranda?</h2></div>
                <div class="card-body">
                    <?php foreach (['aktif' => 'Tampilkan section "Tentang Desainer"', 'nonaktif' => 'Sembunyikan sementara'] as $nilai => $label): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input<?= kelasError($error, 'status') ?>" type="radio" name="status"
                                   id="status-<?= e($nilai) ?>" value="<?= e($nilai) ?>"<?= $data['status'] === $nilai ? ' checked' : '' ?>>
                            <label class="form-check-label" for="status-<?= e($nilai) ?>"><?= e($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($error['status'])): ?>
                        <div class="text-danger small"><?= e($error['status']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
        <a href="../index.php#judul-tentang" class="btn btn-link ms-auto" target="_blank" rel="noopener">
            Lihat di beranda <i class="bi bi-box-arrow-up-right"></i>
        </a>
    </div>
</form>

<?php require __DIR__ . '/includes/bawah.php'; ?>
