<?php
/**
 * Dashboard admin: ringkasan jumlah data, karya terbaru, dan sebaran per kategori.
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

$statusLayanan   = hitungLayananPerStatus();
$jumlahKarya     = hitungPortofolio();
$daftarKategori  = getKategoriDenganJumlah();
$karyaTerbaru    = array_slice(getSemuaPortofolio(), 0, 5);

$judulHalaman = 'Dashboard';
$menuAktif    = 'dashboard';

require __DIR__ . '/includes/atas.php';

$kotakRingkasan = [
    ['angka' => $statusLayanan['aktif'],    'label' => 'Layanan aktif',      'warna' => 'text-bg-primary', 'ikon' => 'bi-briefcase-fill',   'url' => 'layanan.php'],
    ['angka' => $statusLayanan['nonaktif'], 'label' => 'Layanan nonaktif',   'warna' => 'text-bg-secondary', 'ikon' => 'bi-pause-circle-fill', 'url' => 'layanan.php'],
    ['angka' => $jumlahKarya,               'label' => 'Karya di portofolio', 'warna' => 'text-bg-accent',  'ikon' => 'bi-images',           'url' => 'portofolio.php'],
    ['angka' => count($daftarKategori),     'label' => 'Kategori desain',    'warna' => 'text-bg-ink',     'ikon' => 'bi-tags-fill',        'url' => 'kategori.php'],
];
?>

<p class="text-body-secondary mb-4">Halo, <strong><?= e($admin['nama']) ?></strong>. Berikut ringkasan konten website hari ini.</p>

<div class="row">
    <?php foreach ($kotakRingkasan as $kotak): ?>
        <div class="col-lg-3 col-6">
            <div class="small-box <?= e($kotak['warna']) ?>">
                <div class="inner">
                    <h3><?= e($kotak['angka']) ?></h3>
                    <p><?= e($kotak['label']) ?></p>
                </div>
                <i class="small-box-icon bi <?= e($kotak['ikon']) ?>" aria-hidden="true"></i>
                <a href="<?= e($kotak['url']) ?>" class="small-box-footer link-light link-underline-opacity-0">
                    Kelola <i class="bi bi-arrow-right-short"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <h2 class="card-title fs-6 fw-bold mb-0">Karya terbaru</h2>
                <a href="portofolio-form.php" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Tambah karya</a>
            </div>
            <div class="card-body p-0">
                <?php if ($karyaTerbaru): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($karyaTerbaru as $karya): ?>
                            <li class="list-group-item d-flex align-items-center gap-3">
                                <img src="<?= e(urlGambarAdmin($karya['gambar'])) ?>" alt="" class="thumb" width="64" height="48" loading="lazy">
                                <div class="flex-grow-1 min-w-0">
                                    <a href="portofolio-form.php?id=<?= e($karya['id_portofolio']) ?>" class="fw-semibold text-decoration-none d-block text-truncate">
                                        <?= e($karya['judul']) ?>
                                    </a>
                                    <small class="text-body-secondary"><?= e($karya['nama_kategori']) ?> · <?= e(formatTanggal($karya['tanggal'])) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-body-secondary text-center py-4 mb-0">Belum ada karya.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="card-title fs-6 fw-bold mb-0">Isi per kategori</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Kategori</th>
                                <th scope="col" class="text-center">Layanan</th>
                                <th scope="col" class="text-center">Karya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftarKategori as $kategori): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($kategori['nama_kategori']) ?></td>
                                    <td class="text-center"><?= e($kategori['jumlah_layanan']) ?></td>
                                    <td class="text-center"><?= e($kategori['jumlah_portofolio']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
