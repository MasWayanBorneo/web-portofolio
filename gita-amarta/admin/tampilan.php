<?php
/**
 * Pengaturan tampilan website publik: tema & warna, serta logo.
 * Admin memilih tema bawaan, atau "Kustom" lalu mengatur 5 warna sendiri.
 * Warna yang membuat teks sulit dibaca (di bawah standar WCAG AA) ditolak.
 *
 * Halaman ini punya 3 aksi POST (field "aksi"):
 *   tema        → simpan tema & warna (bawaan jika field aksi tidak ada)
 *   logo        → unggah logo baru
 *   logo_bawaan → kembali ke logo bawaan (logo.svg)
 */

require_once __DIR__ . '/includes/auth.php';

$admin      = wajibLogin();
$lama       = getTampilan();
$daftarTema = daftarTema();
$error      = [];
$errorLogo  = null;
$data       = $lama;
$aksi       = ambilTeks($_POST, 'aksi');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'logo') {
    cekCsrf();

    $unggah = unggahGambar($_FILES['logo'] ?? null);

    if ($unggah['error'] !== null) {
        $errorLogo = $unggah['error'];
    } elseif ($unggah['nama'] === null) {
        $errorLogo = 'Pilih file logo terlebih dahulu.';
    } else {
        simpanLogo((int) $lama['id_tampilan'], $unggah['nama']);
        hapusGambarJikaTidakDipakai($lama['logo']);
        setFlash('success', 'Logo baru sudah dipakai di website dan panel admin.');
        redirect('tampilan.php#bagian-logo');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'logo_bawaan') {
    cekCsrf();

    simpanLogo((int) $lama['id_tampilan'], null);
    hapusGambarJikaTidakDipakai($lama['logo']);
    setFlash('success', 'Logo dikembalikan ke logo bawaan.');
    redirect('tampilan.php#bagian-logo');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($aksi, ['logo', 'logo_bawaan'], true)) {
    cekCsrf();

    $input = ambilInputTampilan($_POST);
    $error = validasiTampilan($input);

    if (!$error) {
        simpanTampilan((int) $lama['id_tampilan'], $input);
        $namaTema = $daftarTema[$input['tema']]['nama'] ?? 'Kustom';
        setFlash('success', 'Tema "' . $namaTema . '" sudah dipakai di website.');
        redirect('tampilan.php');
    }

    $data = array_merge($data, $input);
}

$variabel    = variabelCssTema($data);
$hasilKontras = cekKontrasTema($data);

// Variabel CSS untuk kotak pratinjau (nilainya sudah tervalidasi, tetap di-escape saat dicetak).
$gayaPratinjau = '';
foreach ($variabel as $nama => $nilai) {
    $gayaPratinjau .= $nama . ': ' . $nilai . '; ';
}

$labelWarna = [
    'warna_utama' => ['Warna utama', 'Tombol, link, dan judul berwarna.'],
    'warna_aksen' => ['Warna aksen', 'Aksen kedua: garis bawah menu, blob hero, langkah ke-2.'],
    'warna_sorot' => ['Warna sorot', 'Aksen kecil: stabilo judul, ikon footer.'],
    'warna_latar' => ['Warna latar', 'Latar halaman. Harus terang.'],
    'warna_teks'  => ['Warna teks', 'Teks utama dan latar footer. Harus gelap.'],
];

$judulHalaman = 'Tampilan';
$menuAktif    = 'tampilan';

require __DIR__ . '/includes/atas.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <p class="mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Tema belum bisa disimpan.</strong></p>
        <ul class="mb-0 ps-4">
            <?php foreach ($error as $kolom => $pesan): ?>
                <?php foreach ((array) $pesan as $baris): ?>
                    <li><?= e($baris) ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="tampilan.php" method="post" data-form-tema>
    <?= inputCsrf() ?>
    <input type="hidden" name="aksi" value="tema">

    <div class="row g-4">
        <div class="col-xl-7">
            <!-- Pilihan tema -->
            <div class="card mb-4">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">1. Pilih tema</h2></div>
                <div class="card-body">
                    <div class="pilihan-tema" role="radiogroup" aria-label="Tema website">
                        <?php foreach ($daftarTema + ['kustom' => ['nama' => 'Kustom']] as $kunci => $tema): ?>
                            <?php $warnaTema = array_intersect_key($tema, array_flip(KOLOM_WARNA)); ?>
                            <label class="kartu-tema">
                                <input type="radio" name="tema" value="<?= e($kunci) ?>" class="visually-hidden"
                                       <?= $data['tema'] === $kunci ? 'checked' : '' ?>
                                       <?= $warnaTema ? 'data-warna="' . e(json_encode($warnaTema)) . '"' : '' ?>>
                                <span class="kartu-tema-isi">
                                    <span class="kartu-tema-swatch" aria-hidden="true">
                                        <?php if ($warnaTema): ?>
                                            <?php foreach ($warnaTema as $hex): ?>
                                                <span style="background: <?= e($hex) ?>"></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="swatch-kustom"><i class="bi bi-palette"></i></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="kartu-tema-nama"><?= e($tema['nama']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Warna kustom -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 fw-bold mb-0">2. Atur warna <span class="text-body-secondary fw-normal">(otomatis menjadi tema Kustom)</span></h2>
                </div>
                <div class="card-body">
                    <?php foreach ($labelWarna as $kolom => [$label, $bantuan]): ?>
                        <div class="baris-warna<?= isset($error[$kolom]) ? ' is-invalid' : '' ?>">
                            <input type="color" id="<?= e($kolom) ?>" name="<?= e($kolom) ?>" class="form-control form-control-color"
                                   value="<?= e(strtolower(warnaHexValid((string) $data[$kolom]) ? $data[$kolom] : $variabel['--primary'])) ?>"
                                   aria-describedby="bantuan-<?= e($kolom) ?>">
                            <div class="flex-grow-1">
                                <label for="<?= e($kolom) ?>" class="form-label fw-semibold mb-0"><?= e($label) ?></label>
                                <div class="form-text mt-0" id="bantuan-<?= e($kolom) ?>"><?= e($bantuan) ?></div>
                                <?php if (isset($error[$kolom])): ?>
                                    <div class="text-danger small"><?= e($error[$kolom]) ?></div>
                                <?php endif; ?>
                            </div>
                            <code class="kode-warna" data-kode-untuk="<?= e($kolom) ?>"><?= e(strtoupper((string) $data[$kolom])) ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <!-- Pratinjau -->
            <div class="card mb-4">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Pratinjau</h2></div>
                <div class="card-body">
                    <div class="pratinjau" style="<?= e($gayaPratinjau) ?>" data-pratinjau aria-hidden="true">
                        <div class="pv-nav">
                            <span class="pv-logo"></span>
                            <strong>Gita Amarta</strong>
                            <span class="pv-menu">Layanan</span>
                        </div>
                        <div class="pv-hero">
                            <span class="pv-kicker">Layanan Unggulan</span>
                            <span class="pv-judul">Gita <span class="pv-mark">Amarta</span></span>
                            <span class="pv-muted">Studio desain grafis untuk UMKM dan brand lokal.</span>
                            <span class="pv-aksi">
                                <span class="pv-btn">Lihat Layanan</span>
                                <span class="pv-btn pv-btn--outline">Portofolio</span>
                            </span>
                            <span class="pv-kartu">
                                <span class="pv-tag">Logo</span>
                                <strong>Desain Logo</strong>
                                <span class="pv-harga">Rp300.000 – Rp750.000</span>
                            </span>
                            <span class="pv-langkah">
                                <span class="pv-step pv-step--1">1</span>
                                <span class="pv-step pv-step--2">2</span>
                                <span class="pv-step pv-step--3">3</span>
                            </span>
                        </div>
                        <div class="pv-footer"><span class="pv-sun">●</span> halo@gitaamarta.id</div>
                    </div>
                </div>
            </div>

            <!-- Cek kontras -->
            <div class="card">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Cek keterbacaan (WCAG AA)</h2></div>
                <ul class="list-group list-group-flush" data-daftar-kontras>
                    <?php foreach ($hasilKontras as $cek): ?>
                        <li class="list-group-item d-flex align-items-center gap-2"
                            data-depan="<?= e($cek['depan']) ?>"
                            data-belakang="<?= e($cek['belakang']) ?>"
                            data-minimal="<?= e($cek['minimal']) ?>">
                            <span class="flex-grow-1 small"><?= e($cek['label']) ?></span>
                            <span class="small text-body-secondary text-nowrap" data-rasio><?= e(number_format($cek['rasio'], 2, ',', '')) ?>:1</span>
                            <span class="badge <?= $cek['lolos'] ? 'text-bg-success' : 'text-bg-danger' ?>" data-status>
                                <?= $cek['lolos'] ? 'Lolos' : 'Kurang' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="card-footer small text-body-secondary">
                    Tema hanya bisa disimpan jika semua baris <strong>Lolos</strong>, agar teks tetap mudah dibaca pengunjung.
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Pakai tema ini</button>
        <a href="../index.php" class="btn btn-link ms-auto" target="_blank" rel="noopener">Lihat website <i class="bi bi-box-arrow-up-right"></i></a>
    </div>
</form>

<!-- ================= LOGO (form terpisah karena memakai upload file) ================= -->
<?php $pakaiLogoBawaan = urlLogo() === 'assets/img/' . LOGO_BAWAAN; ?>
<div class="card mt-5" id="bagian-logo">
    <div class="card-header d-flex align-items-center">
        <h2 class="card-title fs-6 fw-bold mb-0">3. Logo website</h2>
        <span class="badge ms-auto <?= $pakaiLogoBawaan ? 'text-bg-secondary' : 'text-bg-primary' ?>">
            <?= $pakaiLogoBawaan ? 'Logo bawaan' : 'Logo unggahan' ?>
        </span>
    </div>
    <div class="card-body">
        <?php if ($errorLogo !== null): ?>
            <div class="alert alert-danger py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($errorLogo) ?>
            </div>
        <?php endif; ?>

        <div class="row g-4 align-items-start">
            <div class="col-lg-6">
                <p class="small text-body-secondary mb-2">Pratinjau di navbar (latar terang) dan footer (latar gelap):</p>
                <div class="pratinjau-logo" aria-hidden="true">
                    <div class="latar-terang">
                        <img src="<?= e('../' . urlLogo()) ?>" alt="" width="40" height="40" data-preview-untuk="logo">
                        <span><?= e($profilUsaha['nama_usaha']) ?></span>
                    </div>
                    <div class="latar-gelap">
                        <img src="<?= e('../' . urlLogo()) ?>" alt="" width="40" height="40" data-preview-untuk="logo">
                        <span><?= e($profilUsaha['nama_usaha']) ?></span>
                    </div>
                </div>
                <p class="small text-body-secondary mt-2 mb-0">Logo juga dipakai sebagai ikon di tab browser (favicon) dan di panel admin.</p>
            </div>

            <div class="col-lg-6">
                <form action="tampilan.php" method="post" enctype="multipart/form-data">
                    <?= inputCsrf() ?>
                    <input type="hidden" name="aksi" value="logo">
                    <label for="logo" class="form-label fw-semibold">Unggah logo baru</label>
                    <input type="file" id="logo" name="logo" accept="image/png,image/webp,image/jpeg" required
                           class="form-control<?= $errorLogo !== null ? ' is-invalid' : '' ?>">
                    <div class="form-text">
                        PNG atau WEBP dengan <strong>latar transparan</strong> dan bentuk <strong>persegi</strong>
                        (misalnya 512×512 px), maksimal <?= UKURAN_MAKS_MB ?> MB. JPG bisa, tapi latarnya tidak transparan.
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-upload"></i> Pakai logo ini</button>
                </form>

                <?php if (!$pakaiLogoBawaan): ?>
                    <form action="tampilan.php" method="post" class="mt-3"
                          data-konfirmasi="Kembalikan ke logo bawaan? Logo unggahan saat ini akan dihapus.">
                        <?= inputCsrf() ?>
                        <input type="hidden" name="aksi" value="logo_bawaan">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i> Kembalikan ke logo bawaan
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
