<?php
/**
 * Galeri portofolio (KF-04) dengan filter kategori dan lightbox.
 *
 * Tanpa JavaScript: filter lewat link ?kategori=id, dan klik karya membuka file gambarnya.
 * Dengan JavaScript : filter beranimasi dan klik karya membuka lightbox (dibuat oleh main.js).
 */

require_once __DIR__ . '/includes/functions.php';

$daftarKategori = getSemuaKategori();
$daftarKarya    = getSemuaPortofolio();

$idKategoriAktif = ambilIdDariUrl('kategori');
if ($idKategoriAktif !== null && getKategoriById($idKategoriAktif) === null) {
    $idKategoriAktif = null;
}

$jumlahTampil = 0;
foreach ($daftarKarya as $karya) {
    if ($idKategoriAktif === null || (int) $karya['id_kategori'] === $idKategoriAktif) {
        $jumlahTampil++;
    }
}

$judulHalaman     = 'Portofolio';
$halamanAktif     = 'portofolio';
$deskripsiHalaman = 'Kumpulan karya desain logo, poster, kemasan, dan UI/UX dari Gita Amarta.';

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="hero-blobs" aria-hidden="true">
        <span class="blob blob--1"></span>
        <span class="blob blob--2"></span>
    </div>
    <div class="container">
        <p class="section-kicker hero-anim" style="--d: 0ms">Portofolio</p>
        <h1 class="page-title hero-anim" style="--d: 80ms">Karya yang sudah kami kerjakan</h1>
        <p class="page-lead hero-anim" style="--d: 160ms">
            Klik salah satu karya untuk melihat gambar besar beserta ceritanya.
        </p>
    </div>
</section>

<section class="section section--compact">
    <div class="container">
        <div class="filter-bar">
            <nav class="filter" aria-label="Filter kategori portofolio" data-filter-group="galeri-portofolio">
                <a class="chip<?= $idKategoriAktif === null ? ' is-active' : '' ?>" href="portofolio.php"
                   data-filter="semua"<?= $idKategoriAktif === null ? ' aria-current="true"' : '' ?>>Semua</a>
                <?php foreach ($daftarKategori as $kategori): ?>
                    <?php $aktif = (int) $kategori['id_kategori'] === $idKategoriAktif; ?>
                    <a class="chip<?= $aktif ? ' is-active' : '' ?>"
                       href="portofolio.php?kategori=<?= e($kategori['id_kategori']) ?>"
                       data-filter="<?= e($kategori['id_kategori']) ?>"<?= $aktif ? ' aria-current="true"' : '' ?>>
                        <?= e($kategori['nama_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <p class="filter-count" aria-live="polite" data-filter-count="galeri-portofolio" data-noun="karya">
                Menampilkan <?= e($jumlahTampil) ?> karya
            </p>
        </div>

        <div class="gallery" id="galeri-portofolio" data-gallery>
            <?php foreach ($daftarKarya as $karya): ?>
                <?php $cocok = $idKategoriAktif === null || (int) $karya['id_kategori'] === $idKategoriAktif; ?>
                <a class="porto-item reveal" href="<?= e(urlGambar($karya['gambar'])) ?>"
                   data-kategori="<?= e($karya['id_kategori']) ?>"
                   data-lightbox
                   data-judul="<?= e($karya['judul']) ?>"
                   data-kategori-nama="<?= e($karya['nama_kategori']) ?>"
                   data-tanggal="<?= e(formatTanggal($karya['tanggal'])) ?>"
                   data-deskripsi="<?= e($karya['deskripsi']) ?>"<?= $cocok ? '' : ' hidden' ?>>
                    <?php [$lebar, $tinggi] = ukuranGambar($karya['gambar']); ?>
                    <span class="porto-media">
                        <img src="<?= e(urlGambar($karya['gambar'])) ?>" alt="<?= e($karya['judul']) ?>"
                             width="<?= e($lebar) ?>" height="<?= e($tinggi) ?>" loading="lazy">
                        <span class="porto-zoom" aria-hidden="true"><?= ikon('perbesar') ?></span>
                    </span>
                    <span class="porto-caption">
                        <span class="tag"><?= e($karya['nama_kategori']) ?></span>
                        <span class="porto-title"><?= e($karya['judul']) ?></span>
                        <span class="porto-date"><?= e(formatTanggal($karya['tanggal'])) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <p class="empty-note" data-filter-empty="galeri-portofolio"<?= $jumlahTampil > 0 ? ' hidden' : '' ?>>
            Belum ada karya di kategori ini.
        </p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
