<?php
/**
 * Detail layanan (KF-03) + formulir pemesanan (KF-05, KF-06).
 *
 * Formulir dikirim ke pesan.php. Jika validasi server gagal, pesan.php
 * menyimpan error & isian lama di session lalu kembali ke halaman ini.
 */

require_once __DIR__ . '/includes/functions.php';

mulaiSession(); // harus sebelum ada output HTML

$idLayanan = ambilIdDariUrl('id');
$layanan   = $idLayanan !== null ? getLayananAktifById($idLayanan) : null;

if ($layanan === null) {
    // id tidak valid, tidak ada, atau layanan berstatus nonaktif.
    http_response_code(404);
    $judulHalaman = 'Layanan tidak ditemukan';
    $halamanAktif = 'layanan';

    require __DIR__ . '/includes/header.php';
    ?>
    <section class="page-hero not-found">
        <div class="hero-blobs" aria-hidden="true">
            <span class="blob blob--1"></span>
            <span class="blob blob--2"></span>
        </div>
        <div class="container not-found-inner">
            <p class="not-found-code hero-anim" style="--d: 0ms" aria-hidden="true">404</p>
            <h1 class="page-title hero-anim" style="--d: 80ms">Layanan tidak ditemukan</h1>
            <p class="page-lead hero-anim" style="--d: 160ms">
                Layanan yang kamu cari tidak tersedia atau sedang tidak dibuka. Silakan pilih layanan lain di katalog.
            </p>
            <div class="hero-actions hero-anim" style="--d: 240ms">
                <a class="btn btn-primary btn-lg" href="layanan.php">Kembali ke Katalog</a>
                <a class="btn btn-outline btn-lg" href="index.php">Ke Beranda</a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$contohKarya = getPortofolioByKategori((int) $layanan['id_kategori'], 4);

// Error & isian lama dari pesan.php (jika validasi server gagal).
$flash  = ambilFlashPesanan((int) $layanan['id_layanan']);
$error  = $flash['error'];
$isian  = array_merge(['nama' => '', 'whatsapp' => '', 'kebutuhan' => '', 'tenggat' => ''], $flash['isian']);
$hariIni = date('Y-m-d');

/** Atribut tambahan untuk kolom yang error (aria-invalid). */
$atributError = static fn (string $kolom): string => isset($error[$kolom]) ? ' aria-invalid="true"' : '';
$kelasField   = static fn (string $kolom): string => isset($error[$kolom]) ? 'field is-invalid' : 'field';

$judulHalaman     = $layanan['nama_layanan'];
$halamanAktif     = 'layanan';
$deskripsiHalaman = mb_strimwidth((string) $layanan['deskripsi'], 0, 155, '…');

require __DIR__ . '/includes/header.php';
?>

<section class="detail-section">
    <div class="container">
        <nav class="breadcrumb hero-anim" style="--d: 0ms" aria-label="Breadcrumb">
            <ol>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="layanan.php">Layanan</a></li>
                <li><a href="layanan.php?kategori=<?= e($layanan['id_kategori']) ?>"><?= e($layanan['nama_kategori']) ?></a></li>
                <li aria-current="page"><?= e($layanan['nama_layanan']) ?></li>
            </ol>
        </nav>

        <div class="detail-grid">
            <!-- ===== Kolom kiri: informasi layanan ===== -->
            <article class="detail-info">
                <span class="tag hero-anim" style="--d: 60ms"><?= e($layanan['nama_kategori']) ?></span>
                <h1 class="page-title hero-anim" style="--d: 120ms"><?= e($layanan['nama_layanan']) ?></h1>

                <div class="detail-cover hero-anim" style="--d: 180ms">
                    <img src="<?= e(urlGambar($layanan['gambar'])) ?>" alt="Gambar sampul <?= e($layanan['nama_layanan']) ?>"
                         width="800" height="600" loading="lazy">
                </div>

                <dl class="detail-facts reveal">
                    <div class="fact">
                        <dt><?= ikon('tag') ?> Kisaran harga</dt>
                        <dd class="price price--lg"><?= e(formatKisaranHarga((int) $layanan['harga_min'], (int) $layanan['harga_max'])) ?></dd>
                    </div>
                    <div class="fact">
                        <dt><?= ikon('jam') ?> Estimasi pengerjaan</dt>
                        <dd class="fact-value"><?= e($layanan['estimasi_hari']) ?> hari kerja</dd>
                    </div>
                </dl>

                <div class="prose reveal">
                    <h2>Deskripsi layanan</h2>
                    <?php foreach (preg_split('/\R{2,}/', trim((string) $layanan['deskripsi'])) as $paragraf): ?>
                        <p><?= nl2br(e($paragraf)) ?></p>
                    <?php endforeach; ?>
                </div>
            </article>

            <!-- ===== Kolom kanan: formulir pemesanan ===== -->
            <aside class="order-card reveal" aria-labelledby="judul-form">
                <h2 id="judul-form" class="order-title">Pesan layanan ini</h2>
                <p class="order-sub">Isi formulir, lalu kamu akan diarahkan ke WhatsApp dengan pesan yang sudah tersusun.</p>

                <form id="form-pesan" class="order-form" action="pesan.php" method="post" novalidate data-order-form>
                    <div class="form-alert" role="alert" data-form-alert<?= $error ? '' : ' hidden' ?>>
                        <?= ikon('peringatan') ?>
                        <div>
                            <strong>Pesanan belum bisa dikirim.</strong>
                            <span>Periksa kembali kolom yang ditandai merah di bawah ini.</span>
                        </div>
                    </div>

                    <input type="hidden" name="id_layanan" value="<?= e($layanan['id_layanan']) ?>">

                    <div class="field">
                        <label for="layanan">Layanan</label>
                        <input id="layanan" type="text" value="<?= e($layanan['nama_layanan']) ?>" readonly>
                    </div>

                    <div class="<?= $kelasField('nama') ?>">
                        <label for="nama">Nama lengkap <span class="req" aria-hidden="true">*</span></label>
                        <input id="nama" name="nama" type="text" autocomplete="name" maxlength="100" required
                               value="<?= e($isian['nama']) ?>" aria-describedby="nama-error"<?= $atributError('nama') ?>>
                        <p class="field-error" id="nama-error" data-error-for="nama"><?= e($error['nama'] ?? '') ?></p>
                    </div>

                    <div class="<?= $kelasField('whatsapp') ?>">
                        <label for="whatsapp">Nomor WhatsApp <span class="req" aria-hidden="true">*</span></label>
                        <input id="whatsapp" name="whatsapp" type="tel" inputmode="numeric" autocomplete="tel"
                               maxlength="15" placeholder="Contoh: 081234567890" required
                               value="<?= e($isian['whatsapp']) ?>" aria-describedby="whatsapp-hint whatsapp-error"<?= $atributError('whatsapp') ?>>
                        <p class="field-hint" id="whatsapp-hint">Hanya angka, 10–15 digit.</p>
                        <p class="field-error" id="whatsapp-error" data-error-for="whatsapp"><?= e($error['whatsapp'] ?? '') ?></p>
                    </div>

                    <div class="<?= $kelasField('kebutuhan') ?>">
                        <label for="kebutuhan">Deskripsi kebutuhan <span class="req" aria-hidden="true">*</span></label>
                        <textarea id="kebutuhan" name="kebutuhan" rows="5" maxlength="1000" required
                                  placeholder="Ceritakan usahamu, gaya desain yang diinginkan, warna, ukuran, dll."
                                  aria-describedby="kebutuhan-error"<?= $atributError('kebutuhan') ?>><?= e($isian['kebutuhan']) ?></textarea>
                        <p class="field-error" id="kebutuhan-error" data-error-for="kebutuhan"><?= e($error['kebutuhan'] ?? '') ?></p>
                    </div>

                    <div class="<?= $kelasField('tenggat') ?>">
                        <label for="tenggat">Tenggat waktu <span class="optional">(opsional)</span></label>
                        <input id="tenggat" name="tenggat" type="date" min="<?= e($hariIni) ?>"
                               value="<?= e($isian['tenggat']) ?>" aria-describedby="tenggat-error"<?= $atributError('tenggat') ?>>
                        <p class="field-error" id="tenggat-error" data-error-for="tenggat"><?= e($error['tenggat'] ?? '') ?></p>
                    </div>

                    <p class="form-legend"><span class="req" aria-hidden="true">*</span> wajib diisi</p>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit>
                        <?= ikon('whatsapp') ?> Kirim &amp; Lanjut ke WhatsApp
                    </button>

                    <p class="form-note"><?= ikon('klip') ?> File referensi desain dapat dikirim langsung melalui WhatsApp.</p>
                </form>
            </aside>
        </div>
    </div>
</section>

<!-- ===== Contoh karya dengan kategori yang sama ===== -->
<section class="section section--tint">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <p class="section-kicker">Contoh Karya</p>
                <h2 class="section-title">Hasil desain <?= e($layanan['nama_kategori']) ?> sebelumnya</h2>
            </div>
            <a class="link-arrow" href="portofolio.php?kategori=<?= e($layanan['id_kategori']) ?>">
                Lihat semua portofolio <?= e($layanan['nama_kategori']) ?> <?= ikon('panah') ?>
            </a>
        </div>

        <?php if ($contohKarya): ?>
            <div class="gallery" data-gallery>
                <?php foreach ($contohKarya as $karya): ?>
                    <a class="porto-item reveal" href="<?= e(urlGambar($karya['gambar'])) ?>"
                       data-lightbox
                       data-judul="<?= e($karya['judul']) ?>"
                       data-kategori-nama="<?= e($karya['nama_kategori']) ?>"
                       data-tanggal="<?= e(formatTanggal($karya['tanggal'])) ?>"
                       data-deskripsi="<?= e($karya['deskripsi']) ?>">
                        <span class="porto-media">
                            <img src="<?= e(urlGambar($karya['gambar'])) ?>" alt="<?= e($karya['judul']) ?>"
                                 width="800" height="600" loading="lazy">
                            <span class="porto-zoom" aria-hidden="true"><?= ikon('perbesar') ?></span>
                        </span>
                        <span class="porto-caption">
                            <span class="porto-title"><?= e($karya['judul']) ?></span>
                            <span class="porto-date"><?= e(formatTanggal($karya['tanggal'])) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-note">Belum ada contoh karya untuk kategori ini.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
