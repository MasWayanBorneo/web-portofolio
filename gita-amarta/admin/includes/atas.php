<?php
/**
 * Bagian atas halaman admin (layout AdminLTE 4): <head>, navbar, sidebar, judul halaman.
 *
 * Variabel yang wajib ada sebelum file ini di-include:
 *   $admin        (array)  hasil wajibLogin()
 *   $judulHalaman (string) judul halaman, contoh 'Layanan'
 *   $menuAktif    (string) kunci menu yang disorot: dashboard | layanan | portofolio | kategori | profil | desainer | tampilan | akun
 * Opsional:
 *   $breadcrumb   (array)  [['label' => 'Layanan', 'url' => 'layanan.php'], ['label' => 'Tambah']]
 */

/*
 * Anotasi untuk editor (Intelephense): variabel ini dibuat oleh halaman yang
 * meng-include file ini, bukan di file ini sendiri. Tidak memengaruhi jalannya program.
 */
/** @var array  $admin */
/** @var string $judulHalaman */
/** @var string $menuAktif */

$profilUsaha = getProfilUsaha();
$breadcrumb ??= [['label' => $judulHalaman]];

$menuAdmin = [
    ['kunci' => 'dashboard', 'label' => 'Dashboard', 'url' => 'index.php', 'ikon' => 'bi-speedometer2'],
    ['judul' => 'Kelola Konten'],
    ['kunci' => 'layanan',    'label' => 'Layanan',    'url' => 'layanan.php',    'ikon' => 'bi-briefcase'],
    ['kunci' => 'portofolio', 'label' => 'Portofolio', 'url' => 'portofolio.php', 'ikon' => 'bi-images'],
    ['kunci' => 'kategori',   'label' => 'Kategori',   'url' => 'kategori.php',   'ikon' => 'bi-tags'],
    ['judul' => 'Pengaturan'],
    ['kunci' => 'profil',   'label' => 'Profil Usaha', 'url' => 'profil.php',   'ikon' => 'bi-shop'],
    ['kunci' => 'desainer', 'label' => 'Desainer',     'url' => 'desainer.php', 'ikon' => 'bi-person-badge'],
    ['kunci' => 'tampilan', 'label' => 'Tampilan',     'url' => 'tampilan.php', 'ikon' => 'bi-palette'],
    ['kunci' => 'akun',   'label' => 'Akun Saya',    'url' => 'akun.php',   'ikon' => 'bi-person-gear'],
];
?>
<!doctype html>
<!-- Mode gelap AdminLTE dimatikan: panel admin memakai satu tema terang, sama seperti situs publik. -->
<html lang="id" data-bs-theme="light" data-lte-color-mode="off">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($judulHalaman) ?> | Admin <?= e($profilUsaha['nama_usaha']) ?></title>
    <link rel="icon" href="<?= e('../' . urlLogo()) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(urlAset('assets/adminlte/adminlte.min.css', __DIR__ . '/..')) ?>">
    <link rel="stylesheet" href="<?= e(urlAset('assets/admin.css', __DIR__ . '/..')) ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- ================= NAVBAR ================= -->
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Buka/tutup menu samping">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="../index.php" class="nav-link" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i> Lihat Website
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-inisial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($admin['nama'], 0, 1))) ?></span>
                        <span class="d-none d-md-inline"><?= e($admin['nama']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2">
                            <strong class="d-block"><?= e($admin['nama']) ?></strong>
                            <small class="text-body-secondary">@<?= e($admin['username']) ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="akun.php"><i class="bi bi-person-gear me-2"></i>Akun Saya</a></li>
                        <li>
                            <!-- Logout lewat POST + token CSRF, agar tidak bisa dipicu dari link situs lain. -->
                            <form action="logout.php" method="post">
                                <?= inputCsrf() ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- ================= SIDEBAR ================= -->
    <aside class="app-sidebar shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="index.php" class="brand-link">
                <img src="<?= e('../' . urlLogo()) ?>" alt="" class="brand-image" width="33" height="33">
                <span class="brand-text fw-bold"><?= e($profilUsaha['nama_usaha']) ?></span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2" aria-label="Menu admin">
                <ul class="nav sidebar-menu flex-column" role="list">
                    <?php foreach ($menuAdmin as $item): ?>
                        <?php if (isset($item['judul'])): ?>
                            <li class="nav-header"><?= e(mb_strtoupper($item['judul'])) ?></li>
                        <?php else: ?>
                            <?php $aktif = $item['kunci'] === $menuAktif; ?>
                            <li class="nav-item">
                                <a href="<?= e($item['url']) ?>" class="nav-link<?= $aktif ? ' active' : '' ?>"<?= $aktif ? ' aria-current="page"' : '' ?>>
                                    <i class="nav-icon bi <?= e($item['ikon']) ?>"></i>
                                    <p><?= e($item['label']) ?></p>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- ================= KONTEN ================= -->
    <main class="app-main" id="konten">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center g-2">
                    <div class="col-sm-6">
                        <h1 class="mb-0 fs-3 fw-bold"><?= e($judulHalaman) ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb float-sm-end mb-0">
                                <?php if ($menuAktif !== 'dashboard'): ?>
                                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <?php endif; ?>
                                <?php foreach ($breadcrumb as $i => $langkah): ?>
                                    <?php if ($i === array_key_last($breadcrumb) || empty($langkah['url'])): ?>
                                        <li class="breadcrumb-item active" aria-current="page"><?= e($langkah['label']) ?></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item"><a href="<?= e($langkah['url']) ?>"><?= e($langkah['label']) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <?php foreach (ambilFlash() as $flash): ?>
                    <div class="alert alert-<?= e($flash['tipe']) ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['pesan']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endforeach; ?>
