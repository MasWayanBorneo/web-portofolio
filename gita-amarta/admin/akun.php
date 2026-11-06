<?php
/**
 * Akun admin yang sedang login: ubah nama/email dan ganti password.
 * Dua form terpisah di satu halaman, dibedakan dengan field "aksi".
 */

require_once __DIR__ . '/includes/auth.php';

$admin      = wajibLogin();
$idAdmin    = (int) $admin['id_admin'];
$errorData  = [];
$errorSandi = [];
$data       = ['nama' => $admin['nama'], 'email' => (string) $admin['email']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $aksi = ambilTeks($_POST, 'aksi');

    if ($aksi === 'data') {
        $data      = ['nama' => ambilTeks($_POST, 'nama'), 'email' => ambilTeks($_POST, 'email')];
        $errorData = validasiDataAdmin($data['nama'], $data['email']);

        if (!$errorData) {
            ubahDataAdmin($idAdmin, $data['nama'], $data['email']);
            setFlash('success', 'Data akun sudah diperbarui.');
            redirect('akun.php');
        }
    } elseif ($aksi === 'password') {
        // Password tidak di-trim: spasi di awal/akhir dianggap bagian dari password.
        $sandi = static fn (string $kunci): string => is_string($_POST[$kunci] ?? null) ? $_POST[$kunci] : '';

        $errorSandi = validasiGantiPassword($admin, $sandi('password_lama'), $sandi('password_baru'), $sandi('konfirmasi'));

        if (!$errorSandi) {
            ubahPasswordAdmin($idAdmin, $sandi('password_baru'));
            session_regenerate_id(true);
            setFlash('success', 'Password berhasil diganti. Gunakan password baru saat masuk berikutnya.');
            redirect('akun.php');
        }
    }
}

$judulHalaman = 'Akun Saya';
$menuAktif    = 'akun';

require __DIR__ . '/includes/atas.php';
?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Data akun</h2></div>
            <form action="akun.php" method="post">
                <div class="card-body">
                    <?= inputCsrf() ?>
                    <input type="hidden" name="aksi" value="data">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="username">Username</label>
                        <input type="text" id="username" class="form-control" value="<?= e($admin['username']) ?>" readonly disabled>
                        <div class="form-text">Username dipakai untuk masuk dan tidak bisa diubah.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="nama">Nama <span class="text-danger">*</span></label>
                        <input type="text" id="nama" name="nama" maxlength="100" required
                               class="form-control<?= kelasError($errorData, 'nama') ?>" value="<?= e($data['nama']) ?>">
                        <?= pesanError($errorData, 'nama') ?>
                    </div>
                    <div>
                        <label class="form-label fw-semibold" for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="100"
                               class="form-control<?= kelasError($errorData, 'email') ?>" value="<?= e($data['email']) ?>">
                        <?= pesanError($errorData, 'email') ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Ganti password</h2></div>
            <form action="akun.php" method="post">
                <div class="card-body">
                    <?= inputCsrf() ?>
                    <input type="hidden" name="aksi" value="password">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password_lama">Password lama <span class="text-danger">*</span></label>
                        <input type="password" id="password_lama" name="password_lama" required autocomplete="current-password"
                               class="form-control<?= kelasError($errorSandi, 'password_lama') ?>">
                        <?= pesanError($errorSandi, 'password_lama') ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password_baru">Password baru <span class="text-danger">*</span></label>
                        <input type="password" id="password_baru" name="password_baru" required minlength="8" autocomplete="new-password"
                               class="form-control<?= kelasError($errorSandi, 'password_baru') ?>">
                        <div class="form-text">Minimal 8 karakter.</div>
                        <?= pesanError($errorSandi, 'password_baru') ?>
                    </div>
                    <div>
                        <label class="form-label fw-semibold" for="konfirmasi">Ulangi password baru <span class="text-danger">*</span></label>
                        <input type="password" id="konfirmasi" name="konfirmasi" required minlength="8" autocomplete="new-password"
                               class="form-control<?= kelasError($errorSandi, 'konfirmasi') ?>">
                        <?= pesanError($errorSandi, 'konfirmasi') ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock"></i> Ganti password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
