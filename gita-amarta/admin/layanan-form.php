<?php
/**
 * Form tambah layanan (tanpa ?id) dan edit layanan (?id=...).
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

$idLayanan = ambilIdDariUrl('id');
$lama      = $idLayanan !== null ? getLayananById($idLayanan) : null;
$modeEdit  = $lama !== null;

if ($idLayanan !== null && !$modeEdit) {
    setFlash('danger', 'Layanan tidak ditemukan.');
    redirect('layanan.php');
}

$kategoriList = getSemuaKategori();
$error        = [];

// Nilai awal form: data lama saat edit, atau kosong saat tambah.
$data = $lama ?? [
    'id_kategori'   => null,
    'nama_layanan'  => '',
    'deskripsi'     => '',
    'harga_min'     => '',
    'harga_max'     => '',
    'estimasi_hari' => '',
    'gambar'        => null,
    'status'        => 'aktif',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $input = ambilInputLayanan($_POST);
    $error = validasiLayanan($input);

    // Gambar hanya diproses jika isian lain sudah benar, agar tidak ada file yatim.
    $gambar = $lama['gambar'] ?? null;
    if (!$error) {
        $unggah = unggahGambar($_FILES['gambar'] ?? null);
        if ($unggah['error'] !== null) {
            $error['gambar'] = $unggah['error'];
        } elseif ($unggah['nama'] !== null) {
            $gambar = $unggah['nama'];
        }
    }

    if (!$error) {
        if ($modeEdit) {
            ubahLayanan($idLayanan, $input, $gambar);
            if ($gambar !== $lama['gambar']) {
                hapusGambarJikaTidakDipakai($lama['gambar']);
            }
            setFlash('success', 'Perubahan layanan "' . $input['nama_layanan'] . '" sudah disimpan.');
        } else {
            tambahLayanan($input, $gambar);
            setFlash('success', 'Layanan "' . $input['nama_layanan'] . '" berhasil ditambahkan.');
        }

        redirect('layanan.php');
    }

    // Validasi gagal: tampilkan lagi isian admin. Harga yang bukan angka dikembalikan apa adanya.
    $data = array_merge($data, $input, [
        'harga_min'     => $input['harga_min'] ?? ambilTeks($_POST, 'harga_min'),
        'harga_max'     => $input['harga_max'] ?? ambilTeks($_POST, 'harga_max'),
        'estimasi_hari' => $input['estimasi_hari'] ?? ambilTeks($_POST, 'estimasi_hari'),
        'status'        => $input['status'] !== '' ? $input['status'] : 'aktif',
    ]);
}

$judulHalaman = $modeEdit ? 'Edit Layanan' : 'Tambah Layanan';
$menuAktif    = 'layanan';
$breadcrumb   = [['label' => 'Layanan', 'url' => 'layanan.php'], ['label' => $modeEdit ? 'Edit' : 'Tambah']];

require __DIR__ . '/includes/atas.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Data belum bisa disimpan. Periksa kolom yang ditandai merah.
    </div>
<?php endif; ?>

<form action="layanan-form.php<?= $modeEdit ? '?id=' . e($idLayanan) : '' ?>" method="post" enctype="multipart/form-data">
    <?= inputCsrf() ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Informasi layanan</h2></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nama_layanan" class="form-label fw-semibold">Nama layanan <span class="text-danger">*</span></label>
                        <input type="text" id="nama_layanan" name="nama_layanan" maxlength="100" required
                               class="form-control<?= kelasError($error, 'nama_layanan') ?>" value="<?= e($data['nama_layanan']) ?>">
                        <?= pesanError($error, 'nama_layanan') ?>
                    </div>

                    <div class="mb-3">
                        <label for="id_kategori" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select id="id_kategori" name="id_kategori" required class="form-select<?= kelasError($error, 'id_kategori') ?>">
                            <option value="">— Pilih kategori —</option>
                            <?php foreach ($kategoriList as $kategori): ?>
                                <option value="<?= e($kategori['id_kategori']) ?>"<?= (int) $data['id_kategori'] === (int) $kategori['id_kategori'] ? ' selected' : '' ?>>
                                    <?= e($kategori['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= pesanError($error, 'id_kategori') ?>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="7" required
                                  class="form-control<?= kelasError($error, 'deskripsi') ?>"><?= e($data['deskripsi']) ?></textarea>
                        <div class="form-text">Tekan Enter dua kali untuk membuat paragraf baru.</div>
                        <?= pesanError($error, 'deskripsi') ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="harga_min" class="form-label fw-semibold">Harga minimal (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="harga_min" name="harga_min" min="0" step="1000" required inputmode="numeric"
                                   class="form-control<?= kelasError($error, 'harga_min') ?>" value="<?= e($data['harga_min']) ?>">
                            <?= pesanError($error, 'harga_min') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="harga_max" class="form-label fw-semibold">Harga maksimal (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="harga_max" name="harga_max" min="0" step="1000" required inputmode="numeric"
                                   class="form-control<?= kelasError($error, 'harga_max') ?>" value="<?= e($data['harga_max']) ?>">
                            <?= pesanError($error, 'harga_max') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="estimasi_hari" class="form-label fw-semibold">Estimasi pengerjaan <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input type="number" id="estimasi_hari" name="estimasi_hari" min="1" max="365" required inputmode="numeric"
                                       class="form-control<?= kelasError($error, 'estimasi_hari') ?>" value="<?= e($data['estimasi_hari']) ?>">
                                <span class="input-group-text">hari kerja</span>
                                <?= pesanError($error, 'estimasi_hari') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Status</h2></div>
                <div class="card-body">
                    <?php foreach (['aktif' => 'Aktif – tampil di website', 'nonaktif' => 'Nonaktif – disembunyikan'] as $nilai => $label): ?>
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

            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Gambar sampul</h2></div>
                <div class="card-body">
                    <img src="<?= e(urlGambarAdmin($data['gambar'])) ?>" alt="Pratinjau gambar sampul" class="img-preview mb-3"
                         data-preview-untuk="gambar" width="800" height="600">
                    <label for="gambar" class="form-label fw-semibold"><?= $modeEdit ? 'Ganti gambar' : 'Pilih gambar' ?> <span class="text-body-secondary fw-normal">(opsional)</span></label>
                    <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp"
                           class="form-control<?= kelasError($error, 'gambar') ?>">
                    <div class="form-text">JPG, PNG, atau WEBP, maksimal 2 MB. Rasio 4:3 paling pas.</div>
                    <?= pesanError($error, 'gambar') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $modeEdit ? 'Simpan perubahan' : 'Simpan layanan' ?></button>
        <a href="layanan.php" class="btn btn-outline-secondary">Batal</a>
        <?php if ($modeEdit && $lama['status'] === 'aktif'): ?>
            <a href="../detail.php?id=<?= e($idLayanan) ?>" class="btn btn-link ms-auto" target="_blank" rel="noopener">
                Lihat di website <i class="bi bi-box-arrow-up-right"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<?php require __DIR__ . '/includes/bawah.php'; ?>
