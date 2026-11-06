<?php
/**
 * Beranda (KF-01): hero, layanan unggulan, cara pesan, dan kontak (di footer).
 */

require_once __DIR__ . '/includes/functions.php';

$profil          = getProfilUsaha();
$layananUnggulan = getLayananUnggulan(4);
$karyaTerbaru    = getPortofolioTerbaru();
$jumlahLayanan   = count(getLayananAktif());
$jumlahKarya     = hitungPortofolio();
$jumlahKategori  = count(getSemuaKategori());
$kataJudul       = preg_split('/\s+/', trim((string) $profil['nama_usaha']));
$kategoriKeahlian = getSemuaKategori();

// Data desainer dikelola dari panel admin (menu "Desainer"). Section disembunyikan jika nonaktif/kosong.
$desainer = getDesainer();
if ($desainer !== null && $desainer['status'] !== 'aktif') {
    $desainer = null;
}

$judulHalaman = 'Beranda';
$halamanAktif = 'beranda';

require __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="hero-blobs" aria-hidden="true">
        <span class="blob blob--1"></span>
        <span class="blob blob--2"></span>
        <span class="blob blob--3"></span>
    </div>

    <div class="container hero-grid">
        <div class="hero-text">
            <p class="eyebrow hero-anim" style="--d: 0ms"><span class="dot" aria-hidden="true"></span> Jasa Desain Grafis</p>

            <h1 class="hero-title">
                <?php foreach ($kataJudul as $i => $kata): ?>
                    <span class="word<?= $i === array_key_last($kataJudul) ? ' word--mark' : '' ?>" style="--i: <?= $i ?>"><?= e($kata) ?></span>
                <?php endforeach; ?>
            </h1>

            <p class="hero-lead hero-anim" style="--d: 350ms"><?= e($profil['deskripsi']) ?></p>

            <div class="hero-actions hero-anim" style="--d: 450ms">
                <a class="btn btn-primary btn-lg" href="layanan.php">
                    Lihat Layanan <?= ikon('panah') ?>
                </a>
                <a class="btn btn-outline btn-lg" href="portofolio.php">Lihat Portofolio</a>
            </div>

            <dl class="hero-stats hero-anim" style="--d: 550ms">
                <div>
                    <dt>Layanan aktif</dt>
                    <dd><?= e($jumlahLayanan) ?></dd>
                </div>
                <div>
                    <dt>Karya di portofolio</dt>
                    <dd><?= e($jumlahKarya) ?>+</dd>
                </div>
                <div>
                    <dt>Kategori desain</dt>
                    <dd><?= e($jumlahKategori) ?></dd>
                </div>
            </dl>
        </div>

        <?php if ($karyaTerbaru): ?>
            <figure class="hero-art hero-anim" style="--d: 250ms">
                <div class="hero-frame">
                    <!-- Gambar hero berada di layar pertama, jadi dimuat langsung (bukan lazy) agar cepat tampil. -->
                    <img src="<?= e(urlGambar($karyaTerbaru['gambar'])) ?>"
                         alt="<?= e($karyaTerbaru['judul']) ?>"
                         width="800" height="600" fetchpriority="high">
                </div>
                <figcaption class="float-card float-card--bottom">
                    <span class="float-card-label">Karya terbaru · <?= e($karyaTerbaru['nama_kategori']) ?></span>
                    <strong><?= e($karyaTerbaru['judul']) ?></strong>
                </figcaption>
                <div class="float-card float-card--top" aria-hidden="true">
                    <span class="spark">✦</span> Desain orisinal
                </div>
            </figure>
        <?php endif; ?>
    </div>
</section>

<!-- ================= LAYANAN UNGGULAN ================= -->
<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <p class="section-kicker">Layanan Unggulan</p>
                <h2 class="section-title">Pilih desain yang kamu butuhkan</h2>
            </div>
            <a class="link-arrow" href="layanan.php">Semua layanan <?= ikon('panah') ?></a>
        </div>

        <?php if ($layananUnggulan): ?>
            <div class="card-grid">
                <?php foreach ($layananUnggulan as $layanan): ?>
                    <article class="card reveal">
                        <div class="card-media">
                            <img src="<?= e(urlGambar($layanan['gambar'])) ?>" alt="<?= e($layanan['nama_layanan']) ?>"
                                 width="800" height="600" loading="lazy">
                        </div>
                        <div class="card-body">
                            <span class="tag"><?= e($layanan['nama_kategori']) ?></span>
                            <h3 class="card-title">
                                <a href="detail.php?id=<?= e($layanan['id_layanan']) ?>"><?= e($layanan['nama_layanan']) ?></a>
                            </h3>
                            <p class="price"><?= e(formatKisaranHarga((int) $layanan['harga_min'], (int) $layanan['harga_max'])) ?></p>
                            <p class="card-meta"><?= ikon('jam') ?> Estimasi <?= e($layanan['estimasi_hari']) ?> hari kerja</p>
                            <a class="btn btn-primary btn-sm card-action" href="detail.php?id=<?= e($layanan['id_layanan']) ?>"
                               aria-label="Detail <?= e($layanan['nama_layanan']) ?>">Detail</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-note">Belum ada layanan yang tersedia.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ================= TENTANG DESAINER ================= -->
<?php if ($desainer !== null): ?>
<section class="section about" aria-labelledby="judul-tentang">
    <div class="container about-grid">
        <figure class="about-photo reveal">
            <div class="about-frame">
                <img src="<?= e(urlGambar($desainer['foto'])) ?>"
                     alt="Foto <?= e($desainer['nama']) ?>, <?= e($desainer['peran']) ?>"
                     width="600" height="720" loading="lazy">
            </div>
            <figcaption class="float-card about-badge">
                <strong><?= e($desainer['nama']) ?></strong>
                <span class="float-card-label"><?= e($desainer['peran']) ?></span>
            </figcaption>
        </figure>

        <div class="about-text reveal">
            <p class="section-kicker">Tentang Desainer</p>
            <h2 class="section-title" id="judul-tentang">Kenalan dulu dengan yang mendesain</h2>
            <p class="about-bio"><?= e($desainer['bio']) ?></p>

            <?php if ((string) $desainer['kutipan'] !== ''): ?>
                <blockquote class="about-quote">
                    <p>“<?= e($desainer['kutipan']) ?>”</p>
                </blockquote>
            <?php endif; ?>

            <?php if ($kategoriKeahlian): ?>
                <ul class="about-skills" aria-label="Bidang keahlian">
                    <?php foreach ($kategoriKeahlian as $kategori): ?>
                        <li class="tag"><?= e($kategori['nama_kategori']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="btn btn-outline" href="portofolio.php">Lihat Hasil Karya <?= ikon('panah') ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= CARA PESAN ================= -->
<section class="section section--tint" id="cara-pesan">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <div>
                <p class="section-kicker">Cara Pesan</p>
                <h2 class="section-title">Tiga langkah, langsung ngobrol</h2>
                <p class="section-sub">Tanpa akun dan tanpa ribet. Data pesananmu langsung dikirim ke WhatsApp kami.</p>
            </div>
        </div>

        <ol class="steps">
            <li class="step reveal">
                <span class="step-num step-num--1" aria-hidden="true">1</span>
                <h3>Pilih layanan</h3>
                <p>Lihat katalog, bandingkan kisaran harga dan estimasi pengerjaan, lalu buka detail layanan.</p>
            </li>
            <li class="step reveal">
                <span class="step-num step-num--2" aria-hidden="true">2</span>
                <h3>Isi formulir</h3>
                <p>Tulis nama, nomor WhatsApp, kebutuhan desain, dan tenggat waktu jika ada.</p>
            </li>
            <li class="step reveal">
                <span class="step-num step-num--3" aria-hidden="true">3</span>
                <h3>Lanjut diskusi di WhatsApp</h3>
                <p>Pesanmu tersusun otomatis di WhatsApp. Kirim, lalu kita diskusikan detail dan referensinya.</p>
            </li>
        </ol>

        <div class="steps-cta reveal">
            <a class="btn btn-primary btn-lg" href="layanan.php">Mulai Pilih Layanan <?= ikon('panah') ?></a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
