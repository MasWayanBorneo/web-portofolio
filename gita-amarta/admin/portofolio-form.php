<?php
/**
 * Form tambah karya (tanpa ?id) dan edit karya (?id=...).
 * Berbeda dengan layanan, karya portofolio WAJIB punya gambar.
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

$idPortofolio = ambilIdDariUrl('id');
$lama         = $idPortofolio !== null ? getPortofolioById($idPortofolio) : null;
$modeEdit     = $lama !== null;

if ($idPortofolio !== null && !$modeEdit) {
    setFlash('danger', 'Karya tidak ditemukan.');
    redirect('portofolio.php');
}

$kategoriList = getSemuaKategori();
$error        = [];

$data = $lama ?? [
    'id_kategori' => null,
    'judul'       => '',
    'deskripsi'   => '',
    'gambar'      => null,
    'tanggal'     => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $input = ambilInputPortofolio($_POST);
    $error = validasiPortofolio($input);

    $gambar = $lama['gambar'] ?? null;
    if (!$error) {
        $unggah = unggahGambar($_FILES['gambar'] ?? null);
        if ($unggah['error'] !== null) {
            $error['gambar'] = $unggah['error'];
        } elseif ($unggah['nama'] !== null) {
            $gambar = $unggah['nama'];
        } elseif ($gambar === null) {
            $error['gambar'] = 'Gambar karya wajib diunggah.';
        }
    }

    if (!$error) {
        if ($modeEdit) {
            ubahPortofolio($idPortofolio, $input, $gambar);
            if ($gambar !== $lama['gambar']) {
                hapusGambarJikaTidakDipakai($lama['gambar']);
            }
            setFlash('success', 'Perubahan karya "' . $input['judul'] . '" sudah disimpan.');
        } else {
            tambahPortofolio($input, $gambar);
            setFlash('success', 'Karya "' . $input['judul'] . '" berhasil ditambahkan.');
        }

        redirect('portofolio.php');
    }

    $data = array_merge($data, $input);
}

$judulHalaman = $modeEdit ? 'Edit Karya' : 'Tambah Karya';
$menuAktif    = 'portofolio';
$breadcrumb   = [['label' => 'Portofolio', 'url' => 'portofolio.php'], ['label' => $modeEdit ? 'Edit' : 'Tambah']];

require __DIR__ . '/includes/atas.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Data belum bisa disimpan. Periksa kolom yang ditandai merah.
    </div>
<?php endif; ?>

<form action="portofolio-form.php<?= $modeEdit ? '?id=' . e($idPortofolio) : '' ?>" method="post" enctype="multipart/form-data">
    <?= inputCsrf() ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Informasi karya</h2></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label fw-semibold">Judul karya <span class="text-danger">*</span></label>
                        <input type="text" id="judul" name="judul" maxlength="100" required
                               class="form-control<?= kelasError($error, 'judul') ?>" value="<?= e($data['judul']) ?>">
                        <?= pesanError($error, 'judul') ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
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
                        <div class="col-sm-6">
                            <label for="tanggal" class="form-label fw-semibold">Tanggal selesai <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" name="tanggal" required max="<?= e(date('Y-m-d')) ?>"
                                   class="form-control<?= kelasError($error, 'tanggal') ?>" value="<?= e($data['tanggal']) ?>">
                            <?= pesanError($error, 'tanggal') ?>
                        </div>
                    </div>

                    <div>
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi <span class="text-body-secondary fw-normal">(opsional)</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="5"
                                  class="form-control<?= kelasError($error, 'deskripsi') ?>"><?= e($data['deskripsi']) ?></textarea>
                        <div class="form-text">Tampil di lightbox saat karya diklik di halaman portofolio.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Gambar karya <span class="text-danger">*</span></h2></div>
                <div class="card-body">
                    <img src="<?= e(urlGambarAdmin($data['gambar'])) ?>" alt="Pratinjau gambar karya" class="img-preview mb-3"
                         data-preview-untuk="gambar" width="800" height="600">
                    <label for="gambar" class="form-label fw-semibold"><?= $modeEdit ? 'Ganti gambar' : 'Pilih gambar' ?></label>
                    <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp"
                           class="form-control<?= kelasError($error, 'gambar') ?>"<?= $modeEdit ? '' : ' required' ?>>
                    <div class="form-text">JPG, PNG, atau WEBP, maksimal 2 MB.<?= $modeEdit ? ' Kosongkan jika tidak ingin mengganti.' : '' ?></div>
                    <?= pesanError($error, 'gambar') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $modeEdit ? 'Simpan perubahan' : 'Simpan karya' ?></button>
        <a href="portofolio.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

<?php require __DIR__ . '/includes/bawah.php'; ?>
