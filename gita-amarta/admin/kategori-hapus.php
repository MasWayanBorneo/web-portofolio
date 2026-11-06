<?php
/**
 * Konfirmasi hapus kategori yang MASIH dipakai layanan/karya.
 *
 * Karena setiap layanan & karya wajib punya kategori (foreign key), admin memilih:
 *   - "pindah": isi kategori dipindahkan ke kategori lain, lalu kategori dihapus (aman)
 *   - "semua" : kategori dihapus BESERTA semua layanan & karyanya (tidak bisa dibatalkan)
 *
 * Kategori yang kosong dihapus langsung dari kategori.php tanpa halaman ini.
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

$idKategori = ambilIdDariUrl('id');
$kategori   = $idKategori !== null ? getKategoriById($idKategori) : null;

if ($kategori === null) {
    setFlash('danger', 'Kategori tidak ditemukan.');
    redirect('kategori.php');
}

$daftarLayanan = getSemuaLayananAdmin($idKategori);
$daftarKarya   = getSemuaPortofolio($idKategori);
$jumlahIsi     = count($daftarLayanan) + count($daftarKarya);

// Kategori tujuan pemindahan = semua kategori lain.
$kategoriLain = array_values(array_filter(
    getSemuaKategori(),
    static fn (array $k): bool => (int) $k['id_kategori'] !== $idKategori
));

$error = [];
$cara  = $kategoriLain ? 'pindah' : 'semua';
$idTujuan = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $cara     = ambilTeks($_POST, 'cara');
    $idTujuan = ambilAngka($_POST, 'id_tujuan', 1);

    if ($cara === 'pindah') {
        $tujuan = $idTujuan !== null && $idTujuan !== $idKategori ? getKategoriById($idTujuan) : null;

        if ($tujuan === null) {
            $error['id_tujuan'] = 'Pilih kategori tujuan untuk memindahkan isinya.';
        } else {
            pindahkanIsiLaluHapusKategori($idKategori, $idTujuan);
            setFlash('success', 'Kategori "' . $kategori['nama_kategori'] . '" dihapus. '
                . $jumlahIsi . ' layanan/karya dipindahkan ke "' . $tujuan['nama_kategori'] . '".');
            redirect('kategori.php');
        }
    } elseif ($cara === 'semua') {
        if (ambilTeks($_POST, 'setuju') !== 'ya') {
            $error['setuju'] = 'Centang kotak persetujuan untuk menghapus semua isinya.';
        } else {
            hapusKategoriBesertaIsi($idKategori);
            setFlash('success', 'Kategori "' . $kategori['nama_kategori'] . '" beserta '
                . count($daftarLayanan) . ' layanan dan ' . count($daftarKarya) . ' karya sudah dihapus.');
            redirect('kategori.php');
        }
    } else {
        $error['cara'] = 'Pilih cara menghapus kategori.';
    }
}

// Kategori ternyata sudah kosong (misalnya isinya baru dipindah di tab lain): hapus biasa.
if ($jumlahIsi === 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('kategori.php');
}

$judulHalaman = 'Hapus Kategori';
$menuAktif    = 'kategori';
$breadcrumb   = [['label' => 'Kategori', 'url' => 'kategori.php'], ['label' => 'Hapus']];

require __DIR__ . '/includes/atas.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e(implode(' ', $error)) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5 order-lg-2">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title fs-6 fw-bold mb-0">Isi kategori "<?= e($kategori['nama_kategori']) ?>"</h2>
            </div>
            <div class="card-body">
                <p class="small text-body-secondary mb-2"><?= count($daftarLayanan) ?> layanan</p>
                <?php if ($daftarLayanan): ?>
                    <ul class="small mb-3">
                        <?php foreach ($daftarLayanan as $layanan): ?>
                            <li>
                                <?= e($layanan['nama_layanan']) ?>
                                <?php if ($layanan['status'] !== 'aktif'): ?>
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <p class="small text-body-secondary mb-2"><?= count($daftarKarya) ?> karya portofolio</p>
                <?php if ($daftarKarya): ?>
                    <ul class="small mb-0">
                        <?php foreach ($daftarKarya as $karya): ?>
                            <li><?= e($karya['judul']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7 order-lg-1">
        <form action="kategori-hapus.php?id=<?= e($idKategori) ?>" method="post" class="card" data-form-hapus-kategori>
            <?= inputCsrf() ?>

            <div class="card-header">
                <h2 class="card-title fs-6 fw-bold mb-0">Apa yang terjadi dengan isinya?</h2>
            </div>

            <div class="card-body">
                <p>
                    Kategori <strong><?= e($kategori['nama_kategori']) ?></strong> masih dipakai oleh
                    <strong><?= count($daftarLayanan) ?> layanan</strong> dan <strong><?= count($daftarKarya) ?> karya</strong>.
                    Setiap layanan dan karya wajib punya kategori, jadi pilih salah satu:
                </p>

                <!-- Pilihan 1: pindahkan (aman) -->
                <div class="pilihan-hapus<?= $kategoriLain ? '' : ' opacity-50' ?>">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cara" id="cara-pindah" value="pindah"
                               <?= $cara === 'pindah' ? 'checked' : '' ?><?= $kategoriLain ? '' : ' disabled' ?>>
                        <label class="form-check-label fw-semibold" for="cara-pindah">
                            Pindahkan isinya ke kategori lain, lalu hapus kategori ini
                            <span class="badge text-bg-success ms-1">Disarankan</span>
                        </label>
                    </div>
                    <?php if ($kategoriLain): ?>
                        <div class="ms-4 mt-2">
                            <label for="id_tujuan" class="form-label small mb-1">Pindahkan ke kategori</label>
                            <select id="id_tujuan" name="id_tujuan" class="form-select<?= kelasError($error, 'id_tujuan') ?>">
                                <option value="">— Pilih kategori tujuan —</option>
                                <?php foreach ($kategoriLain as $k): ?>
                                    <option value="<?= e($k['id_kategori']) ?>"<?= $idTujuan === (int) $k['id_kategori'] ? ' selected' : '' ?>>
                                        <?= e($k['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= pesanError($error, 'id_tujuan') ?>
                            <div class="form-text">Semua layanan dan karya tetap ada, hanya kategorinya yang berubah.</div>
                        </div>
                    <?php else: ?>
                        <p class="ms-4 mt-1 mb-0 small text-body-secondary">Tidak tersedia: ini satu-satunya kategori.</p>
                    <?php endif; ?>
                </div>

                <!-- Pilihan 2: hapus semua (berbahaya) -->
                <div class="pilihan-hapus pilihan-hapus--bahaya">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cara" id="cara-semua" value="semua"
                               <?= $cara === 'semua' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold text-danger" for="cara-semua">
                            Hapus kategori ini beserta semua layanan dan karyanya
                        </label>
                    </div>
                    <div class="ms-4 mt-2">
                        <div class="form-check">
                            <input class="form-check-input<?= kelasError($error, 'setuju') ?>" type="checkbox" name="setuju" id="setuju" value="ya">
                            <label class="form-check-label small" for="setuju">
                                Saya mengerti <?= count($daftarLayanan) ?> layanan dan <?= count($daftarKarya) ?> karya
                                beserta gambarnya akan <strong>dihapus permanen</strong> dan tidak bisa dikembalikan.
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus kategori</button>
                <a href="kategori.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
