<?php
/**
 * Kelola kategori dalam satu halaman: daftar di kiri, form tambah/edit di kanan.
 *   kategori.php          → form dalam mode tambah
 *   kategori.php?edit=3   → form dalam mode edit kategori id 3
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

$idEdit   = ambilIdDariUrl('edit');
$lama     = $idEdit !== null ? getKategoriById($idEdit) : null;
$modeEdit = $lama !== null;
$error    = [];
$data     = $lama ?? ['nama_kategori' => '', 'deskripsi' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $aksi = ambilTeks($_POST, 'aksi');

    if ($aksi === 'hapus') {
        $idHapus  = ambilAngka($_POST, 'id_kategori', 1);
        $kategori = $idHapus !== null ? getKategoriById($idHapus) : null;

        if ($kategori === null) {
            setFlash('danger', 'Kategori tidak ditemukan.');
        } elseif (hitungPemakaianKategori($idHapus) > 0) {
            // Masih ada isinya: admin harus memilih dulu isinya dipindah atau ikut dihapus.
            redirect('kategori-hapus.php?id=' . $idHapus);
        } else {
            hapusKategori($idHapus);
            setFlash('success', 'Kategori "' . $kategori['nama_kategori'] . '" sudah dihapus.');
        }

        redirect('kategori.php');
    }

    // aksi "simpan" (tambah atau edit)
    $input = ambilInputKategori($_POST);
    $error = validasiKategori($input, $modeEdit ? $idEdit : null);

    if (!$error) {
        if ($modeEdit) {
            ubahKategori($idEdit, $input);
            setFlash('success', 'Kategori "' . $input['nama_kategori'] . '" sudah diperbarui.');
        } else {
            tambahKategori($input);
            setFlash('success', 'Kategori "' . $input['nama_kategori'] . '" berhasil ditambahkan.');
        }

        redirect('kategori.php');
    }

    $data = $input;
}

if ($idEdit !== null && !$modeEdit) {
    setFlash('danger', 'Kategori tidak ditemukan.');
    redirect('kategori.php');
}

$daftarKategori = getKategoriDenganJumlah();

$judulHalaman = 'Kategori';
$menuAktif    = 'kategori';

require __DIR__ . '/includes/atas.php';
?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title fs-6 fw-bold mb-0">Semua kategori (<?= count($daftarKategori) ?>)</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col" class="text-center">Layanan</th>
                                <th scope="col" class="text-center">Karya</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftarKategori as $kategori): ?>
                                <?php $dipakai = (int) $kategori['jumlah_layanan'] + (int) $kategori['jumlah_portofolio']; ?>
                                <tr<?= $modeEdit && $idEdit === (int) $kategori['id_kategori'] ? ' class="table-active"' : '' ?>>
                                    <td>
                                        <span class="fw-semibold d-block"><?= e($kategori['nama_kategori']) ?></span>
                                        <?php if ($kategori['deskripsi']): ?>
                                            <small class="text-body-secondary"><?= e($kategori['deskripsi']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= e($kategori['jumlah_layanan']) ?></td>
                                    <td class="text-center"><?= e($kategori['jumlah_portofolio']) ?></td>
                                    <td class="text-end text-nowrap">
                                        <a href="kategori.php?edit=<?= e($kategori['id_kategori']) ?>" class="btn btn-outline-primary btn-sm"
                                           aria-label="Edit <?= e($kategori['nama_kategori']) ?>"><i class="bi bi-pencil"></i></a>
                                        <?php if ($dipakai > 0): ?>
                                            <!-- Masih dipakai: buka halaman konfirmasi (pindahkan isi atau hapus semua). -->
                                            <a href="kategori-hapus.php?id=<?= e($kategori['id_kategori']) ?>" class="btn btn-outline-danger btn-sm"
                                               title="Hapus (masih dipakai <?= e($dipakai) ?> layanan/karya)"
                                               aria-label="Hapus <?= e($kategori['nama_kategori']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <form action="kategori.php" method="post" class="d-inline"
                                                  data-konfirmasi="Hapus kategori &quot;<?= e($kategori['nama_kategori']) ?>&quot;?">
                                                <?= inputCsrf() ?>
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id_kategori" value="<?= e($kategori['id_kategori']) ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Hapus <?= e($kategori['nama_kategori']) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer small text-body-secondary">
                <i class="bi bi-info-circle"></i> Saat menghapus kategori yang masih berisi layanan atau karya,
                kamu akan diminta memilih: pindahkan isinya ke kategori lain, atau hapus semuanya.
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title fs-6 fw-bold mb-0"><?= $modeEdit ? 'Edit kategori' : 'Tambah kategori' ?></h2>
            </div>
            <form action="kategori.php<?= $modeEdit ? '?edit=' . e($idEdit) : '' ?>" method="post">
                <div class="card-body">
                    <?= inputCsrf() ?>
                    <input type="hidden" name="aksi" value="simpan">

                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label fw-semibold">Nama kategori <span class="text-danger">*</span></label>
                        <input type="text" id="nama_kategori" name="nama_kategori" maxlength="50" required
                               class="form-control<?= kelasError($error, 'nama_kategori') ?>" value="<?= e($data['nama_kategori']) ?>">
                        <?= pesanError($error, 'nama_kategori') ?>
                    </div>

                    <div>
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi <span class="text-body-secondary fw-normal">(opsional)</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="3" class="form-control"><?= e($data['deskripsi']) ?></textarea>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $modeEdit ? 'Simpan perubahan' : 'Tambah kategori' ?></button>
                    <?php if ($modeEdit): ?>
                        <a href="kategori.php" class="btn btn-outline-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
