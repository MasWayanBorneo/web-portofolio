<?php
/**
 * Bagian atas setiap halaman: <head> dan navbar.
 *
 * Variabel yang boleh diatur sebelum file ini di-include:
 *   $judulHalaman     (string) judul di tab browser
 *   $halamanAktif     (string) 'beranda' | 'layanan' | 'portofolio'
 *   $deskripsiHalaman (string) meta description (opsional)
 */

require_once __DIR__ . '/functions.php';

$profil           = getProfilUsaha();
$judulHalaman     = $judulHalaman ?? 'Beranda';
$halamanAktif     = $halamanAktif ?? '';
$deskripsiHalaman = $deskripsiHalaman ?? (string) $profil['deskripsi'];
$tampilan         = getTampilan();          // tema & warna yang dipilih admin
$cssTema          = cssTema($tampilan);     // '' jika memakai tema bawaan

$menu = [
    ['kunci' => 'beranda',    'label' => 'Beranda',    'url' => 'index.php'],
    ['kunci' => 'layanan',    'label' => 'Layanan',    'url' => 'layanan.php'],
    ['kunci' => 'portofolio', 'label' => 'Portofolio', 'url' => 'portofolio.php'],
    ['kunci' => 'cara-pesan', 'label' => 'Cara Pesan', 'url' => 'index.php#cara-pesan'],
    ['kunci' => 'kontak',     'label' => 'Kontak',     'url' => '#kontak'],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judulHalaman) ?> | <?= e($profil['nama_usaha']) ?> – Jasa Desain Grafis</title>
    <meta name="description" content="<?= e($deskripsiHalaman) ?>">
    <meta name="theme-color" content="<?= e(variabelCssTema($tampilan)['--bg']) ?>">
    <link rel="icon" href="<?= e(urlLogo()) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(urlAset('assets/css/style.css')) ?>">
    <?php if ($cssTema !== ''): ?>
        <!-- Warna tema dari panel admin (menimpa warna bawaan di style.css). -->
        <style><?= $cssTema ?></style>
    <?php endif; ?>

    <!-- Penanda bahwa JavaScript aktif, dipakai CSS untuk animasi yang butuh JS. -->
    <script>document.documentElement.classList.add('js');</script>
    <script src="<?= e(urlAset('assets/js/main.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#konten">Langsung ke konten</a>

<header class="site-header" data-header>
    <div class="container nav-bar">
        <a class="brand" href="index.php" aria-label="<?= e($profil['nama_usaha']) ?> – ke beranda">
            <img src="<?= e(urlLogo()) ?>" width="40" height="40" alt="">
            <span><?= e($profil['nama_usaha']) ?></span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-utama"
                aria-label="Buka menu" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>

        <nav class="nav-menu" id="menu-utama" aria-label="Menu utama" data-nav-menu>
            <ul>
                <?php foreach ($menu as $item): ?>
                    <?php $aktif = $item['kunci'] === $halamanAktif; ?>
                    <li>
                        <a href="<?= e($item['url']) ?>"<?= $aktif ? ' class="is-active" aria-current="page"' : '' ?>>
                            <?= e($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="konten">
