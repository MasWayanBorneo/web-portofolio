<?php
/**
 * Edit profil usaha: nama, deskripsi, WhatsApp tujuan pemesanan, email, Instagram, alamat.
 * Data ini tampil di hero beranda, footer kontak, dan dipakai untuk link wa.me.
 */

require_once __DIR__ . '/includes/auth.php';

$admin  = wajibLogin();
$profil = getProfilUsaha();
$error  = [];
$data   = $profil;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $input = ambilInputProfil($_POST);
    $error = validasiProfil($input);

    if (!$error) {
        simpanProfilUsaha((int) $profil['id_profil'], $input);
        setFlash('success', 'Profil usaha sudah disimpan dan langsung tampil di website.');
        redirect('profil.php');
    }

    $data = array_merge($data, $input);
}

$judulHalaman = 'Profil Usaha';
$menuAktif    = 'profil';

require __DIR__ . '/includes/atas.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Data belum bisa disimpan. Periksa kolom yang ditandai merah.
    </div>
<?php endif; ?>

<form action="profil.php" method="post">
    <?= inputCsrf() ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Identitas usaha</h2></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nama_usaha" class="form-label fw-semibold">Nama usaha <span class="text-danger">*</span></label>
                        <input type="text" id="nama_usaha" name="nama_usaha" maxlength="100" required
                               class="form-control<?= kelasError($error, 'nama_usaha') ?>" value="<?= e($data['nama_usaha']) ?>">
                        <?= pesanError($error, 'nama_usaha') ?>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi singkat <span class="text-danger">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" required
                                  class="form-control<?= kelasError($error, 'deskripsi') ?>"><?= e($data['deskripsi']) ?></textarea>
                        <div class="form-text">Tampil di bawah judul beranda dan di footer. Cukup 1–2 kalimat.</div>
                        <?= pesanError($error, 'deskripsi') ?>
                    </div>
                    <div>
                        <label for="alamat" class="form-label fw-semibold">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="2" class="form-control"><?= e($data['alamat']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h2 class="card-title fs-6 fw-bold mb-0">Kontak</h2></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="no_whatsapp" class="form-label fw-semibold">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                            <input type="tel" id="no_whatsapp" name="no_whatsapp" maxlength="20" required inputmode="numeric"
                                   class="form-control<?= kelasError($error, 'no_whatsapp') ?>" value="<?= e($data['no_whatsapp']) ?>"
                                   aria-describedby="bantuan-wa">
                            <?= pesanError($error, 'no_whatsapp') ?>
                        </div>
                        <div class="form-text" id="bantuan-wa">Format 62xxx tanpa 0 di depan. Semua pesanan dari formulir dikirim ke nomor ini.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" maxlength="100"
                                   class="form-control<?= kelasError($error, 'email') ?>" value="<?= e($data['email']) ?>">
                            <?= pesanError($error, 'email') ?>
                        </div>
                    </div>
                    <div>
                        <label for="instagram" class="form-label fw-semibold">Instagram</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">@</span>
                            <input type="text" id="instagram" name="instagram" maxlength="31"
                                   class="form-control<?= kelasError($error, 'instagram') ?>" value="<?= e($data['instagram']) ?>">
                            <?= pesanError($error, 'instagram') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan profil</button>
    </div>
</form>

<?php require __DIR__ . '/includes/bawah.php'; ?>
