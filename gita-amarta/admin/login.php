<?php
/**
 * Halaman login admin (layout "login-page" AdminLTE).
 */

require_once __DIR__ . '/includes/auth.php';

mulaiSessionAdmin();

// Sudah login? Langsung ke dashboard.
if (is_int($_SESSION['admin_id'] ?? null)) {
    redirect('index.php');
}

$error    = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $username = ambilTeks($_POST, 'username');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    $error = cobaLogin($username, $password);
    if ($error === null) {
        redirect('index.php');
    }
}

$profilUsaha = getProfilUsaha();
$flash       = ambilFlash();
?>
<!doctype html>
<html lang="id" data-bs-theme="light" data-lte-color-mode="off">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk | Admin <?= e($profilUsaha['nama_usaha']) ?></title>
    <link rel="icon" href="../assets/img/logo.svg" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/adminlte/adminlte.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
<main class="login-box">
    <h1 class="login-logo">
        <a href="../index.php" class="d-inline-flex align-items-center gap-2 text-decoration-none">
            <img src="../assets/img/logo.svg" width="44" height="44" alt="">
            <span><b><?= e($profilUsaha['nama_usaha']) ?></b></span>
        </a>
    </h1>

    <div class="card shadow-sm">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Masuk untuk mengelola layanan dan portofolio</p>

            <?php foreach ($flash as $item): ?>
                <div class="alert alert-<?= e($item['tipe']) ?> py-2" role="alert"><?= e($item['pesan']) ?></div>
            <?php endforeach; ?>

            <?php if ($error !== null): ?>
                <div class="alert alert-danger py-2 d-flex gap-2 align-items-start" role="alert">
                    <i class="bi bi-exclamation-circle-fill mt-1" aria-hidden="true"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <?= inputCsrf() ?>

                <label class="form-label fw-semibold" for="username">Username</label>
                <div class="input-group mb-3">
                    <input id="username" name="username" type="text" class="form-control" value="<?= e($username) ?>"
                           autocomplete="username" required autofocus maxlength="50">
                    <div class="input-group-text"><span class="bi bi-person"></span></div>
                </div>

                <label class="form-label fw-semibold" for="password">Password</label>
                <div class="input-group mb-4">
                    <input id="password" name="password" type="password" class="form-control"
                           autocomplete="current-password" required>
                    <button type="button" class="btn btn-outline-secondary" data-toggle-password="#password"
                            aria-label="Tampilkan password" aria-pressed="false">
                        <span class="bi bi-eye"></span>
                    </button>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
                </div>
            </form>

            <p class="mb-0 mt-3 text-center">
                <a href="../index.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke website</a>
            </p>
        </div>
    </div>
</main>

<script src="assets/admin.js"></script>
</body>
</html>
