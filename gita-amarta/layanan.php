<?php
/**
 * Katalog layanan (KF-02).
 *
 * Filter kategori:
 * - Tanpa JavaScript : link chip ?kategori=id → server menyembunyikan kartu kategori lain (atribut hidden).
 * - Dengan JavaScript: klik chip ditangani main.js (animasi) tanpa memuat ulang halaman.
 * Semua kartu layanan aktif tetap dicetak agar filter di sisi klien bisa berpindah kategori.
 */

require_once __DIR__ . '/includes/functions.php';

$daftarKategori = getSemuaKategori();
$daftarLayanan  = getLayananAktif();

// Kategori terpilih dari URL; diabaikan jika id kategori tidak ada di database.
$idKategoriAktif = ambilIdDariUrl('kategori');
if ($idKategoriAktif !== null && getKategoriById($idKategoriAktif) === null) {
    $idKategoriAktif = null;
}

$jumlahTampil = 0;
foreach ($daftarLayanan as $layanan) {
    if ($idKategoriAktif === null || (int) $layanan['id_kategori'] === $idKategoriAktif) {
        $jumlahTampil++;
    }
}

$judulHalaman     = 'Katalog Layanan';
$halamanAktif     = 'layanan';
$deskripsiHalaman = 'Katalog layanan desain grafis: logo, poster, kemasan, dan UI/UX lengkap dengan kisaran harga.';

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="hero-blobs" aria-hidden="true">
        <span class="blob blob--1"></span>
        <span class="blob blob--2"></span>
    </div>
    <div class="container">
        <p class="section-kicker hero-anim" style="--d: 0ms">Katalog</p>
        <h1 class="page-title hero-anim" style="--d: 80ms">Layanan desain untuk setiap kebutuhan</h1>
        <p class="page-lead hero-anim" style="--d: 160ms">
            Pilih layanan, cek kisaran harga dan estimasi pengerjaan, lalu pesan langsung lewat WhatsApp.
        </p>
    </div>
</section>

<section class="section section--compact">
    <div class="container">
        <div class="filter-bar">
            <nav class="filter" aria-label="Filter kategori layanan" data-filter-group="daftar-layanan">
                <a class="chip<?= $idKategoriAktif === null ? ' is-active' : '' ?>" href="layanan.php"
                   data-filter="semua"<?= $idKategoriAktif === null ? ' aria-current="true"' : '' ?>>Semua</a>
                <?php foreach ($daftarKategori as $kategori): ?>
                    <?php $aktif = (int) $kategori['id_kategori'] === $idKategoriAktif; ?>
                    <a class="chip<?= $aktif ? ' is-active' : '' ?>"
                       href="layanan.php?kategori=<?= e($kategori['id_kategori']) ?>"
                       data-filter="<?= e($kategori['id_kategori']) ?>"<?= $aktif ? ' aria-current="true"' : '' ?>>
                        <?= e($kategori['nama_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <p class="filter-count" aria-live="polite" data-filter-count="daftar-layanan" data-noun="layanan">
                Menampilkan <?= e($jumlahTampil) ?> layanan
            </p>
        </div>

        <div class="card-grid" id="daftar-layanan">
            <?php foreach ($daftarLayanan as $layanan): ?>
                <?php $cocok = $idKategoriAktif === null || (int) $layanan['id_kategori'] === $idKategoriAktif; ?>
                <article class="card reveal" data-kategori="<?= e($layanan['id_kategori']) ?>"<?= $cocok ? '' : ' hidden' ?>>
                    <div class="card-media">
                        <img src="<?= e(urlGambar($layanan['gambar'])) ?>" alt="<?= e($layanan['nama_layanan']) ?>"
                             width="800" height="600" loading="lazy">
                    </div>
                    <div class="card-body">
                        <span class="tag"><?= e($layanan['nama_kategori']) ?></span>
                        <h2 class="card-title">
                            <a href="detail.php?id=<?= e($layanan['id_layanan']) ?>"><?= e($layanan['nama_layanan']) ?></a>
                        </h2>
                        <p class="price"><?= e(formatKisaranHarga((int) $layanan['harga_min'], (int) $layanan['harga_max'])) ?></p>
                        <p class="card-meta"><?= ikon('jam') ?> Estimasi <?= e($layanan['estimasi_hari']) ?> hari kerja</p>
                        <a class="btn btn-primary btn-sm card-action" href="detail.php?id=<?= e($layanan['id_layanan']) ?>"
                           aria-label="Detail <?= e($layanan['nama_layanan']) ?>">Detail</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="empty-note" data-filter-empty="daftar-layanan"<?= $jumlahTampil > 0 ? ' hidden' : '' ?>>
            Belum ada layanan aktif di kategori ini. Coba kategori lain, ya.
        </p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
